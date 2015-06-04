<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2014 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('includes/init.php');
	
	require('config.php');
	require('includes/db-core.php');
	require('includes/helper-functions.php');
	require('includes/check-session.php');
	
	require('includes/filter-functions.php');
	require('includes/entry-functions.php');
	require('includes/users-functions.php');

	$form_id = (int) trim($_GET['id']);

	if(!empty($_POST['form_id'])){
		$form_id = (int) $_POST['form_id'];
	}
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to edit this form.";

			$ssl_suffix = mf_get_ssl_suffix();						
			header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	
	//get form properties
	$query 	= "select 
					form_name,
					form_page_total,
					logic_field_enable,
					logic_page_enable,
					logic_email_enable,
					form_review,
					payment_enable_merchant,
					payment_merchant_type
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] 	= mf_trim_max_length($row['form_name'],55);

		$form_name 			= htmlspecialchars($row['form_name']);
		$logic_field_enable = (int) $row['logic_field_enable'];
		$logic_page_enable  = (int) $row['logic_page_enable'];
		$logic_email_enable = (int) $row['logic_email_enable'];
		$form_page_total    = (int) $row['form_page_total'];
		$form_review   		= (int) $row['form_review'];
		$payment_merchant_type = $row['payment_merchant_type'];
		
		$payment_enable_merchant  = (int) $row['payment_enable_merchant'];
		if($payment_enable_merchant < 1){
			$payment_enable_merchant = 0;
		}

		//page logic is only available on multipage form
		if(!empty($logic_page_enable) && $form_page_total <= 1){
			$logic_page_enable = 0;
		}

		$jquery_data_code .= "\$('.logic_settings').data('logic_status',{\"logic_field_enable\": {$logic_field_enable} ,\"logic_page_enable\": {$logic_page_enable} ,\"logic_email_enable\": {$logic_email_enable}});\n";
	}

	//get the label of all pages within this form
	$all_page_labels = array();
	for ($i=1;$i <= $form_page_total;$i++) { 
		$all_page_labels[$i] = 'Page '.$i;
	}

	if(!empty($form_review)){
		$all_page_labels['review'] = 'Review Page';
	}

	if(!empty($payment_enable_merchant) && $payment_merchant_type != 'check'){
		$all_page_labels['payment'] = 'Payment Page';
	}
	$all_page_labels['success'] = 'Success Page';

	//get the list of all fields within the form (without any child elements)
	$query = "select 
					element_id,
					if(element_type = 'matrix',element_guidelines,element_title) element_title,
					element_type,
					element_page_number,
					element_position
 				from 
 					".MF_TABLE_PREFIX."form_elements 
			   where 
					form_id = ? and 
					element_status = 1 and 
					element_is_private = 0 and 
					element_type <> 'page_break' and 
					element_matrix_parent_id = 0 
		    order by 
		    		element_position asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
	
	$all_fields_array = array();
	while($row = mf_do_fetch_result($sth)){
		$element_page_number = (int) $row['element_page_number'];
		$element_id 		 = (int) $row['element_id'];
		$element_position 	 = (int) $row['element_position'] + 1;

		$element_title = htmlspecialchars(strip_tags($row['element_title']));
		
		if(empty($element_title)){
			$element_title = '-untitled field-';
		}

		if(strlen($element_title) > 70){
			$element_title = substr($element_title, 0, 70).'...';
		}											
		

		$all_fields_array[$element_page_number][$element_id]['element_title'] = $element_position.'. '.$element_title;
		$all_fields_array[$element_page_number][$element_id]['element_type']  = $row['element_type'];
	}


	//get a list of all matrix checkboxes ids
	$query = "select 
					element_id,
					element_constraint 
				from 
					".MF_TABLE_PREFIX."form_elements 
			   where 
			   		element_type = 'matrix' and 
			   		element_matrix_parent_id = 0 and 
			   		element_matrix_allow_multiselect = 1 and 
			   		element_status = 1 and 
			   		form_id = ?";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$matrix_checkboxes_id_array = array();
	while($row = mf_do_fetch_result($sth)){
		$matrix_checkboxes_id_array[] = $row['element_id'];
		if(!empty($row['element_constraint'])){
			$exploded = array();
			$exploded = explode(',', $row['element_constraint']);
			foreach ($exploded as $value) {
				$matrix_checkboxes_id_array[] = $value;
			}
		}
	}

	//get a list of all time fields and the properties
	$query = "select 
					element_id,
					element_time_showsecond,
					element_time_24hour 
				from 
					".MF_TABLE_PREFIX."form_elements 
			   where 
			   		form_id = ? and 
			   		element_type = 'time' and 
			   		element_status = 1";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$time_field_properties = array();
	while($row = mf_do_fetch_result($sth)){
		$time_field_properties[$row['element_id']]['showsecond'] = (int) $row['element_time_showsecond'];
		$time_field_properties[$row['element_id']]['24hour'] 	 = (int) $row['element_time_24hour'];
	}


	//get the list of all fields within the form (including child elements for checkboxes, matrix, etc)
	$columns_meta  = mf_get_columns_meta($dbh,$form_id);
	$columns_label = $columns_meta['name_lookup'];
	$columns_type  = $columns_meta['type_lookup'];

	$field_labels = array_slice($columns_label, 4); //the first four labels are system field. we don't need it.

	//prepare the jquery data for column type lookup
	foreach ($columns_type as $element_name => $element_type) {
		if($element_type == 'matrix'){
			//if this is matrix field which allow multiselect, change the type to checkbox
			$temp = array();
			$temp = explode('_', $element_name);
			$matrix_element_id = $temp[1];

			if(in_array($matrix_element_id, $matrix_checkboxes_id_array)){
				$element_type = 'checkbox';
			}
		}else if($element_type == 'time'){
			//there are several variants of time fields, we need to make it specific
			$temp = array();
			$temp = explode('_', $element_name);
			$time_element_id = $temp[1];

			if(!empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
				$element_type = 'time_showsecond24hour';
			}else if(!empty($time_field_properties[$time_element_id]['showsecond']) && empty($time_field_properties[$time_element_id]['24hour'])){
				$element_type = 'time_showsecond';
			}else if(empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
				$element_type = 'time_24hour';
			}

		}

		$jquery_data_code .= "\$('#ls_fields_lookup').data('$element_name','$element_type');\n";
	}

	/** Field Logic **/
	//get data from ap_field_logic_elements table
	$query = "SELECT 
					A.form_id,
					A.element_id,
					A.rule_show_hide,
					A.rule_all_any,
					if(B.element_type = 'matrix',B.element_guidelines,B.element_title) element_title,
					B.element_position + 1 as element_position,
					B.element_page_number 
				FROM 
					".MF_TABLE_PREFIX."field_logic_elements A LEFT JOIN ".MF_TABLE_PREFIX."form_elements B
				  ON 
				  	A.form_id = B.form_id and A.element_id=B.element_id and B.element_status = 1
			   WHERE
					A.form_id = ?
			ORDER BY 
					B.element_position asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
	
	$logic_elements_array = array();
	$all_logic_elements_id = array();

	while($row = mf_do_fetch_result($sth)){
		$element_id = (int) $row['element_id'];
		
		$logic_elements_array[$element_id]['rule_show_hide'] 	= $row['rule_show_hide'];
		$logic_elements_array[$element_id]['rule_all_any'] 		= $row['rule_all_any'];
		$logic_elements_array[$element_id]['element_position'] 	= $row['element_position'];
		$logic_elements_array[$element_id]['element_page_number'] = $row['element_page_number'];

		$element_title = htmlspecialchars(strip_tags($row['element_title']));
		
		if(empty($element_title)){
			$element_title = '-untitled field-';
		}

		if(strlen($element_title) > 70){
			$element_title = substr($element_title, 0, 70).'...';
		}
		$logic_elements_array[$element_id]['element_title'] = $row['element_position'].'. '.$element_title;	

		$all_logic_elements_id[] = $element_id;
	}

	//get data from ap_field_logic_conditions table
	$query = "select target_element_id,element_name,rule_condition,rule_keyword from ".MF_TABLE_PREFIX."field_logic_conditions where form_id = ? order by alc_id asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
	
	$logic_conditions_array = array();
	$prev_element_id = 0;

	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$target_element_id = (int) $row['target_element_id'];
		
		if($target_element_id != $prev_element_id){
			$i=0;
		}

		$logic_conditions_array[$target_element_id][$i]['element_name']   = $row['element_name'];
		$logic_conditions_array[$target_element_id][$i]['rule_condition'] = $row['rule_condition'];
		$logic_conditions_array[$target_element_id][$i]['rule_keyword']   = $row['rule_keyword'];

		$prev_element_id = $target_element_id;
		$i++;
	}

	/** Page Logic **/
	//get data from ap_page_logic table
	$query = "SELECT 
					form_id,
					page_id,
					rule_all_any 
				FROM 
					".MF_TABLE_PREFIX."page_logic
			   WHERE
					form_id = ?
			ORDER BY
					page_id ASC";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
	
	$logic_pages_array = array();
	$all_logic_pages_id = array();

	while($row = mf_do_fetch_result($sth)){
		$page_id = $row['page_id'];
		
		$logic_pages_array[$page_id]['rule_all_any'] = $row['rule_all_any'];
		$all_logic_pages_id[] = $page_id;
	}

	//get data from ap_page_logic_conditions table
	$query = "select target_page_id,element_name,rule_condition,rule_keyword from ".MF_TABLE_PREFIX."page_logic_conditions where form_id = ? order by apc_id asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
	
	$page_logic_conditions_array = array();
	$prev_page_id = 0;

	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$target_page_id = $row['target_page_id'];
		
		if($target_page_id != $prev_page_id){
			$i=0;
		}

		$page_logic_conditions_array[$target_page_id][$i]['element_name']   = $row['element_name'];
		$page_logic_conditions_array[$target_page_id][$i]['rule_condition'] = $row['rule_condition'];
		$page_logic_conditions_array[$target_page_id][$i]['rule_keyword']   = $row['rule_keyword'];

		$prev_page_id = $target_page_id;
		$i++;
	}

	/** Email Logic **/

	$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);

	//get data from ap_email_logic table
	$query = "SELECT 
					form_id,
					rule_id,
					rule_all_any,
					target_email,
					template_name,
					custom_from_name,
					custom_from_email,
					custom_replyto_email,
					custom_subject,
					custom_content,
					custom_plain_text
				FROM 
					".MF_TABLE_PREFIX."email_logic
			   WHERE
					form_id = ?
			ORDER BY
					rule_id ASC";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
	
	$logic_emails_array = array();
	$email_logic_conditions_array = array();
	
	while($row = mf_do_fetch_result($sth)){
		$rule_id = $row['rule_id'];
		
		$logic_emails_array[$rule_id]['rule_all_any'] 			= $row['rule_all_any'];
		$logic_emails_array[$rule_id]['target_email'] 			= htmlspecialchars($row['target_email']);
		$logic_emails_array[$rule_id]['template_name'] 			= $row['template_name'];
		$logic_emails_array[$rule_id]['custom_from_name'] 		= htmlspecialchars($row['custom_from_name']);
		$logic_emails_array[$rule_id]['custom_from_email'] 		= htmlspecialchars($row['custom_from_email']);
		$logic_emails_array[$rule_id]['custom_replyto_email'] 	= htmlspecialchars($row['custom_replyto_email']);
		$logic_emails_array[$rule_id]['custom_subject'] 		= htmlspecialchars($row['custom_subject']);
		$logic_emails_array[$rule_id]['custom_content'] 		= htmlspecialchars($row['custom_content'],ENT_QUOTES);
		$logic_emails_array[$rule_id]['custom_plain_text'] 		= (int) $row['custom_plain_text'];
	
		if(empty($logic_emails_array[$rule_id]['custom_from_name'])){
			$logic_emails_array[$rule_id]['custom_from_name'] = 'MachForm';
		}

		if(empty($logic_emails_array[$rule_id]['custom_from_email'])){
			$logic_emails_array[$rule_id]['custom_from_email'] = "no-reply@{$domain}";
		}

		if(empty($logic_emails_array[$rule_id]['custom_replyto_email'])){
			$logic_emails_array[$rule_id]['custom_replyto_email'] = "no-reply@{$domain}";
		}
	}

	//if there is no logic email data, we need to initialize it with 1 rule
	if(empty($logic_emails_array)){
		$logic_email_enable = 0;

		$logic_emails_array[1]['rule_all_any'] 			= 'all';
		$logic_emails_array[1]['target_email'] 			= '';
		$logic_emails_array[1]['template_name'] 		= 'notification';
		$logic_emails_array[1]['custom_from_name'] 		= 'MachForm';
		$logic_emails_array[1]['custom_from_email'] 	= "no-reply@{$domain}";
		$logic_emails_array[1]['custom_replyto_email'] 	= "no-reply@{$domain}";
		$logic_emails_array[1]['custom_subject'] 		= '{form_name} [#{entry_no}]';
		$logic_emails_array[1]['custom_content'] 		= '{entry_data}';
		$logic_emails_array[1]['custom_plain_text'] 	= 0;

		$field_names = array_keys($field_labels);
		$first_field_name = $field_names[0];
		$first_field_type = $columns_type[$first_field_name];

		$default_condition = 'is';
		if($first_field_type == 'checkbox'){
			$default_condition = 'is_one';
		}

		$email_logic_conditions_array[1][0]['element_name']   = $first_field_name;
		$email_logic_conditions_array[1][0]['rule_condition'] = $default_condition;
		$email_logic_conditions_array[1][0]['rule_keyword']   = '';
	}

	//get data from ap_email_logic_conditions table
	$query = "select target_rule_id,element_name,rule_condition,rule_keyword from ".MF_TABLE_PREFIX."email_logic_conditions where form_id = ? order by aec_id asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);
		
	$prev_rule_id = 0;

	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$target_rule_id = $row['target_rule_id'];
		
		if($target_rule_id != $prev_rule_id){
			$i=0;
		}

		$email_logic_conditions_array[$target_rule_id][$i]['element_name']   = $row['element_name'];
		$email_logic_conditions_array[$target_rule_id][$i]['rule_condition'] = $row['rule_condition'];
		$email_logic_conditions_array[$target_rule_id][$i]['rule_keyword']   = $row['rule_keyword'];

		$prev_rule_id = $target_rule_id;
		$i++;
	}
	

	//get email fields for this form
	//populate 'Send Email To' dropdown
	$query = "select 
					element_id,
					element_title 
				from 
					`".MF_TABLE_PREFIX."form_elements` 
			   where 
			   		form_id=? and element_type='email' and element_is_private=0 and element_status=1
			order by 
					element_title asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$i=1;
	$email_fields = array();
	while($row = mf_do_fetch_result($sth)){
		$email_fields[$i]['label'] = $row['element_title'];
		$email_fields[$i]['value'] = $row['element_id'];
		$i++;
	}
	
	if(!empty($email_fields)){
		$target_email_address_list = $email_fields;
		
		$target_email_address_list[$i]['label'] = '&#8674; Set Custom Address';
		$target_email_address_list[$i]['value'] = 'custom';

		$target_email_address_list_values = array();
		foreach ($target_email_address_list as $value) {
			$target_email_address_list_values[] = $value['value'];
		}
	}
	
	//get "from name" fields for this form, which are name fields and single line text fields
	$query = "select 
					element_id,
					element_title 
				from 
					`".MF_TABLE_PREFIX."form_elements` 
			   where 
			   		form_id=? and element_is_private=0 and element_status=1
			   		and element_type in('text','simple_name','simple_name_wmiddle','name','name_wmiddle')
			order by 
					element_title asc";
	$params = array($form_id);
	$sth = mf_do_query($query,$params,$dbh);

	$i=1;
	$name_fields = array();
	while($row = mf_do_fetch_result($sth)){
		$name_fields[$i]['label'] = $row['element_title'];
		$name_fields[$i]['value'] = $row['element_id'];
		$i++;
	}

	$custom_from_name_list = array();
	$custom_from_name_list[0]['label'] = 'MachForm';
	$custom_from_name_list[0]['value'] = 'MachForm';
	$custom_from_name_list = array_merge($custom_from_name_list,$name_fields);
		
	$array_max_index = count($custom_from_name_list);

	$custom_from_name_list[$array_max_index]['label'] = '&#8674; Set Custom Name';
	$custom_from_name_list[$array_max_index]['value'] = 'custom';

	$custom_from_name_list_values = array();
	foreach ($custom_from_name_list as $value) {
		$custom_from_name_list_values[] = $value['value'];
	}

	//reply-to email address
	$custom_replyto_email_list = array();
	$custom_replyto_email_list[0]['label'] = "no-reply@{$domain}";
	$custom_replyto_email_list[0]['value'] = "no-reply@{$domain}";
	$custom_replyto_email_list = array_merge($custom_replyto_email_list,$email_fields);
		
	$array_max_index = count($custom_replyto_email_list);

	$custom_replyto_email_list[$array_max_index]['label'] = '&#8674; Set Custom Address';
	$custom_replyto_email_list[$array_max_index]['value'] = 'custom';

	$custom_replyto_email_list_values = array();
	foreach ($custom_replyto_email_list as $value) {
		$custom_replyto_email_list_values[] = $value['value'];
	}

	/** Data for template variables **/
	//get all available complex columns label
	$query  = "select 
					 element_id,
					 element_title,
					 element_type 
			     from
			     	 `".MF_TABLE_PREFIX."form_elements` 
			    where 
			    	 form_id=? and 
			    	 element_type != 'section' and 
			    	 element_status=1
			 order by 
			 		 element_position asc";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	
	$complex_field_columns_label = array();
	while($row = mf_do_fetch_result($sth)){
		$element_title = $row['element_title'];
		$element_id    = $row['element_id'];
		$element_type  = $row['element_type']; 

		//limit the title length to 40 characters max
		if(strlen($element_title) > 40){
			$element_title = substr($element_title,0,40).'...';
		}

		$element_title = htmlspecialchars($element_title,ENT_QUOTES);
		
		//for some field type, we need to provide more detailed template variables
		//the special field types are Name and Address
		if('simple_name' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (First)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (Last)";
		}else if('simple_name_wmiddle' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (First)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (Middle)";
			$complex_field_columns_label['element_'.$element_id.'_3'] = $element_title." (Last)";			
		}else if('name' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (Title)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (First)";
			$complex_field_columns_label['element_'.$element_id.'_3'] = $element_title." (Last)";
			$complex_field_columns_label['element_'.$element_id.'_4'] = $element_title." (Suffix)";
		}else if('name_wmiddle' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (Title)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (First)";
			$complex_field_columns_label['element_'.$element_id.'_3'] = $element_title." (Middle)";
			$complex_field_columns_label['element_'.$element_id.'_4'] = $element_title." (Last)";
			$complex_field_columns_label['element_'.$element_id.'_5'] = $element_title." (Suffix)";
		}else if('address' == $element_type){
			$complex_field_columns_label['element_'.$element_id.'_1'] = $element_title." (Street)";
			$complex_field_columns_label['element_'.$element_id.'_2'] = $element_title." (Address Line 2)";
			$complex_field_columns_label['element_'.$element_id.'_3'] = $element_title." (City)";
			$complex_field_columns_label['element_'.$element_id.'_4'] = $element_title." (State)";
			$complex_field_columns_label['element_'.$element_id.'_5'] = $element_title." (Postal/Zip Code)";
			$complex_field_columns_label['element_'.$element_id.'_6'] = $element_title." (Country)";
		}
	}

	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css" rel="stylesheet" />
<link type="text/css" href="js/datepick/smoothness.datepick.css" rel="stylesheet" />
EOT;

	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post logic_settings">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <img src="images/icons/resultset_next.gif" /> Logic Builder</h2>
							<p>Define conditions and actions for your form fields, pages or notification emails</p>
						</div>	
						<div style="float: right;margin-right: 5px">
								<a href="#" id="button_save_logics" name="button_save_logics" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Save Settings
								</a>
						</div>
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				<div class="content_body">
					
					<form id="ls_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
					<ul id="ls_main_list">
						<li>
							<div id="ls_box_field_rules" class="ns_box_main gradient_blue">
								<div class="ns_box_title">
									<input type="checkbox" value="1" class="checkbox" id="logic_field_enable" <?php if(!empty($logic_field_enable)){ echo 'checked="checked"'; } ?> name="logic_field_enable">
									<label for="logic_field_enable" class="choice">Enable Rules to Show/Hide Fields</label>
									<img class="helpmsg" src="images/icons/68_blue.png" style="vertical-align: top" title="Enable this option to show or hide fields on the form based on the value of another fields. Useful for displaying different set of fields based on user choices."/>
								</div>
								<div class="ls_box_content" <?php if(empty($logic_field_enable)){ echo 'style="display: none"'; } ?>>
									<label class="description" for="ls_select_field_rule" style="margin-top: 2px">
										Select a Field to Show/Hide
									</label>
									<select class="select medium" id="ls_select_field_rule" name="ls_select_field_rule" autocomplete="off">
										<option value=""></option>
										<?php
											for ($i=1; $i <= $form_page_total ; $i++) { 
												if($form_page_total > 1){
													echo '<optgroup label="Page '.$i.'">'."\n";
												}

												$current_page_fields = array();
												$current_page_fields = $all_fields_array[$i];
												
												foreach ($current_page_fields as $element_id => $value) {
													if(!empty($all_logic_elements_id)){
														if(in_array($element_id, $all_logic_elements_id)){
															continue;
														}
													}

													$element_title = $value['element_title'];
													echo '<option value="'.$element_id.'">'.$element_title.'</option>'."\n";
												}
												
												if($form_page_total > 1){
													echo '</optgroup>'."\n";
												}
											}
										?>
									</select>
									<select class="select medium" id="ls_select_field_rule_lookup" name="ls_select_field_rule_lookup" autocomplete="off" style="display: none">
										<option value=""></option>
										<?php
											for ($i=1; $i <= $form_page_total ; $i++) { 
												if($form_page_total > 1){
													echo '<optgroup label="Page '.$i.'">'."\n";
												}

												$current_page_fields = array();
												$current_page_fields = $all_fields_array[$i];
												
												foreach ($current_page_fields as $element_id => $value) {

													$element_title = $value['element_title'];
													echo '<option value="'.$element_id.'">'.$element_title.'</option>'."\n";
												}
												
												if($form_page_total > 1){
													echo '</optgroup>'."\n";
												}
											}
										?>
									</select>
									<select id="ls_fields_lookup" name="ls_fields_lookup" autocomplete="off" class="element select condition_fieldname" style="width: 260px;display:none">
										<?php
											foreach ($field_labels as $element_name => $element_label) {
												
												if($columns_type[$element_name] == 'signature' || $columns_type[$element_name] == 'file'){
													continue;
												}

												$element_label = strip_tags($element_label);
												if(strlen($element_label) > 40){
													$element_label = substr($element_label, 0, 40).'...';
												}
												
												echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
											}
										?>
									</select>
									<ul id="ls_field_rules_group">
										<?php
											if(!empty($logic_elements_array)){

												foreach ($logic_elements_array as $element_id => $value) {
													
													$element_title 		 = $value['element_title'];
													$element_position 	 = $value['element_position'];
													$element_page_number = $value['element_page_number'];
													$rule_show_hide		 = $value['rule_show_hide'];
													$rule_all_any		 = $value['rule_all_any'];
													
													$jquery_data_code .= "\$(\"#lifieldrule_{$element_id}\").data('rule_properties',{\"element_id\": {$element_id},\"rule_show_hide\":\"{$rule_show_hide}\",\"rule_all_any\":\"{$rule_all_any}\"});\n";
												?>

													<li id="lifieldrule_<?php echo $element_id; ?>">
														<table width="100%" cellspacing="0">
															<thead>
																<tr>
																	<td title="Field #<?php echo $element_position; ?> on Page <?php echo $element_page_number; ?>">
																		<strong title="Field #<?php echo $element_position; ?> on Page <?php echo $element_page_number; ?>"><?php echo $element_title; ?></strong><a class="delete_lifieldrule" id="deletelifieldrule_<?php echo $element_id; ?>" href="#"><img src="images/icons/52_blue_16.png"></a>
																	</td>
																</tr>
															</thead>
															<tbody>
																<tr>
																	<td>
																		<h6>
																			<img src="images/icons/arrow_right_blue.png" style="vertical-align: top" />
																			<select style="margin-left: 5px;margin-right: 5px" name="fieldruleshowhide_<?php echo $element_id; ?>" id="fieldruleshowhide_<?php echo $element_id; ?>" class="element select rule_show_hide">
																				<option value="show" <?php if($rule_show_hide == 'show'){ echo 'selected="selected"'; } ?>>Show</option>
																				<option value="hide" <?php if($rule_show_hide == 'hide'){ echo 'selected="selected"'; } ?>>Hide</option>
																			</select> this field if 
																			<select style="margin-left: 5px;margin-right: 5px" name="fieldruleallany_<?php echo $element_id; ?>" id="fieldruleallany_<?php echo $element_id; ?>" class="element select rule_all_any">
																				<option value="all" <?php if($rule_all_any == 'all'){ echo 'selected="selected"'; } ?>>all</option>
																				<option value="any" <?php if($rule_all_any == 'any'){ echo 'selected="selected"'; } ?>>any</option>
																			</select> of the following conditions match: 
																		</h6>
																		<ul class="ls_field_rules_conditions">
																			<?php
																				$current_element_conditions = array();
																				$current_element_conditions = $logic_conditions_array[$element_id];

																				$i = 1;
																				foreach ($current_element_conditions as $value) {
																					$condition_element_name = $value['element_name'];
																					$rule_condition 		= $value['rule_condition'];
																					$rule_keyword 			= htmlspecialchars($value['rule_keyword'],ENT_QUOTES);

																					$field_element_type = $columns_type[$value['element_name']];
											
																					if($field_element_type == 'matrix'){
																						//if this is matrix field which allow multiselect, change the type to checkbox
																						$temp = array();
																						$temp = explode('_', $condition_element_name);
																						$matrix_element_id = $temp[1];

																						if(in_array($matrix_element_id, $matrix_checkboxes_id_array)){
																							$field_element_type = 'checkbox';
																						}
																					}else if($field_element_type == 'time'){
																						//there are several variants of time fields, we need to make it specific
																						$temp = array();
																						$temp = explode('_', $condition_element_name);
																						$time_element_id = $temp[1];

																						if(!empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
																							$field_element_type = 'time_showsecond24hour';
																						}else if(!empty($time_field_properties[$time_element_id]['showsecond']) && empty($time_field_properties[$time_element_id]['24hour'])){
																							$field_element_type = 'time_showsecond';
																						}else if(empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
																							$field_element_type = 'time_24hour';
																						}

																					}

																					$rule_condition_data = new stdClass();
																					$rule_condition_data->target_element_id = $element_id;
																					$rule_condition_data->element_name 		= $condition_element_name;
																					$rule_condition_data->condition 		= $rule_condition;
																					$rule_condition_data->keyword 			= htmlspecialchars_decode($rule_keyword,ENT_QUOTES);

																					$json_rule_condition = json_encode($rule_condition_data);

																					$jquery_data_code .= "\$(\"#lifieldrule_{$element_id}_{$i}\").data('rule_condition',{$json_rule_condition});\n";

																					$condition_date_class = '';
																					$time_hour   = '';
																					$time_minute = '';
																					$time_second = '';
																					$time_ampm   = 'AM';
																					
																					if(in_array($field_element_type, array('money','number'))){
																						$condition_text_display = 'display:none';
																						$condition_number_display = '';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																					}else if(in_array($field_element_type, array('date','europe_date'))){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = '';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																						$condition_date_class = 'class="condition_date"';
																					}else if(in_array($field_element_type, array('time','time_showsecond','time_24hour','time_showsecond24hour'))){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = '';
																						$condition_time_display = '';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = 'display:none';
																						$condition_date_class = '';

																						if(!empty($rule_keyword)){
																							$exploded = array();
																							$exploded = explode(':', $rule_keyword);

																							$time_hour   = sprintf("%02s", $exploded[0]);
																							$time_minute = sprintf("%02s", $exploded[1]);
																							$time_second = sprintf("%02s", $exploded[2]);
																							$time_ampm   = strtoupper($exploded[3]); 
																						}
																						
																						//show or hide the second and AM/PM
																						$condition_second_display = '';
																						$condition_ampm_display   = '';
																						
																						if($field_element_type == 'time'){
																							$condition_second_display = 'display:none';
																						}else if($field_element_type == 'time_24hour'){
																							$condition_second_display = 'display:none';
																							$condition_ampm_display   = 'display:none';
																						}else if($field_element_type == 'time_showsecond24hour'){
																							$condition_ampm_display   = 'display:none';
																						} 
																					}else if($field_element_type == 'file'){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																					}else if($field_element_type == 'checkbox'){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = '';
																						$condition_keyword_display = 'display:none';
																					}else{
																						$condition_text_display = '';
																						$condition_number_display = 'display:none';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																					}
																			?>
																			
																				<li id="lifieldrule_<?php echo $element_id.'_'.$i; ?>" <?php echo $condition_date_class; ?>>
																					<select id="conditionfield_<?php echo $element_id.'_'.$i; ?>" name="conditionfield_<?php echo $element_id.'_'.$i; ?>" autocomplete="off" class="element select condition_fieldname" style="width: 260px;">
																						<?php
																							foreach ($field_labels as $element_name => $element_label) {
																								
																								if($columns_type[$element_name] == 'signature' || $columns_type[$element_name] == 'file'){
																									continue;
																								}

																								$element_label = strip_tags($element_label);
																								if(strlen($element_label) > 40){
																									$element_label = substr($element_label, 0, 40).'...';
																								}
																								
																								if($condition_element_name == $element_name){
																									$selected_tag = 'selected="selected"';
																								}else{
																									$selected_tag = '';
																								}

																								echo "<option {$selected_tag} value=\"{$element_name}\">{$element_label}</option>\n";
																							}
																						?>
																					</select>
																					<select name="conditiontext_<?php echo $element_id.'_'.$i; ?>" id="conditiontext_<?php echo $element_id.'_'.$i; ?>" class="element select condition_text" style="width: 120px;<?php echo $condition_text_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																						<option <?php if($value['rule_condition'] == 'is_not'){ echo 'selected="selected"'; } ?> value="is_not">Is Not</option>
																						<option <?php if($value['rule_condition'] == 'begins_with'){ echo 'selected="selected"'; } ?> value="begins_with">Begins with</option>
																						<option <?php if($value['rule_condition'] == 'ends_with'){ echo 'selected="selected"'; } ?> value="ends_with">Ends with</option>
																						<option <?php if($value['rule_condition'] == 'contains'){ echo 'selected="selected"'; } ?> value="contains">Contains</option>
																						<option <?php if($value['rule_condition'] == 'not_contain'){ echo 'selected="selected"'; } ?> value="not_contain">Does not contain</option>
																					</select>
																					<select name="conditionnumber_<?php echo $element_id.'_'.$i; ?>" id="conditionnumber_<?php echo $element_id.'_'.$i; ?>" class="element select condition_number" style="width: 120px;<?php echo $condition_number_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																						<option <?php if($value['rule_condition'] == 'less_than'){ echo 'selected="selected"'; } ?> value="less_than">Less than</option>
																						<option <?php if($value['rule_condition'] == 'greater_than'){ echo 'selected="selected"'; } ?> value="greater_than">Greater than</option>
																					</select>
																					<select name="conditiondate_<?php echo $element_id.'_'.$i; ?>" id="conditiondate_<?php echo $element_id.'_'.$i; ?>" class="element select condition_date" style="width: 120px;<?php echo $condition_date_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																						<option <?php if($value['rule_condition'] == 'is_before'){ echo 'selected="selected"'; } ?> value="is_before">Is Before</option>
																						<option <?php if($value['rule_condition'] == 'is_after'){ echo 'selected="selected"'; } ?> value="is_after">Is After</option>
																					</select>
																					<select name="conditioncheckbox_<?php echo $element_id.'_'.$i; ?>" id="conditioncheckbox_<?php echo $element_id.'_'.$i; ?>" class="element select condition_checkbox" style="width: 120px;<?php echo $condition_checkbox_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is_one'){ echo 'selected="selected"'; } ?> value="is_one">Is Checked</option>
																						<option <?php if($value['rule_condition'] == 'is_zero'){ echo 'selected="selected"'; } ?> value="is_zero">Is Empty</option>
																					</select> 
																					<span name="conditiontime_<?php echo $element_id.'_'.$i; ?>" id="conditiontime_<?php echo $element_id.'_'.$i; ?>" class="condition_time" style="<?php echo $condition_time_display; ?>">
																						<input name="conditiontimehour_<?php echo $element_id.'_'.$i; ?>" id="conditiontimehour_<?php echo $element_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_hour; ?>" placeholder="HH"> : 
																						<input name="conditiontimeminute_<?php echo $element_id.'_'.$i; ?>" id="conditiontimeminute_<?php echo $element_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_minute; ?>" placeholder="MM">  
																						<span class="conditiontime_second" style="<?php echo $condition_second_display; ?>"> : <input name="conditiontimesecond_<?php echo $element_id.'_'.$i; ?>" id="conditiontimesecond_<?php echo $element_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_second; ?>" placeholder="SS"> </span>
																						<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_<?php echo $element_id.'_'.$i; ?>" id="conditiontimeampm_<?php echo $element_id.'_'.$i; ?>" style="<?php echo $condition_ampm_display; ?>">
																							<option <?php if($time_ampm == 'AM'){ echo 'selected="selected"'; } ?> value="AM">AM</option>
																							<option <?php if($time_ampm == 'PM'){ echo 'selected="selected"'; } ?> value="PM">PM</option>
																						</select>
																					</span>
																					<input type="text" class="element text condition_keyword" value="<?php echo $rule_keyword; ?>" id="conditionkeyword_<?php echo $element_id.'_'.$i; ?>" name="conditionkeyword_<?php echo $element_id.'_'.$i; ?>" style="<?php echo $condition_keyword_display; ?>">
																					<input type="hidden" value="" class="rule_datepicker" name="datepicker_<?php echo $element_id.'_'.$i; ?>" id="datepicker_<?php echo $element_id.'_'.$i; ?>">
								 		 											<span style="display:none"><img id="datepickimg_<?php echo $element_id.'_'.$i; ?>" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>
																					<a href="#" id="deletecondition_<?php echo $element_id.'_'.$i; ?>" name="deletecondition_<?php echo $element_id.'_'.$i; ?>" class="a_delete_condition"><img src="images/icons/51_blue_16.png" /></a>
																				</li>
																			
																			<?php 
																					$i++;
																				} 
																			?>

																			<li class="ls_add_condition">
																				<a href="#" id="addcondition_<?php echo $element_id; ?>" class="a_add_condition"><img src="images/icons/49_blue_16.png" /></a>
																			</li>
																		</ul>
																	</td>
																</tr>
															</tbody>
														</table>
													</li>

												
												<?php
													
												}
											}
										?>	
									</ul>
								</div>
							</div>
						</li>
						<li>&nbsp;</li>
						<li>
							<div id="ls_box_page_rules" class="ns_box_main gradient_red">
								<div class="ns_box_title">
									<input type="checkbox" value="1" class="checkbox" id="logic_page_enable" name="logic_page_enable" <?php if(!empty($logic_page_enable)){ echo 'checked="checked"'; } ?>>
									<label for="logic_page_enable" class="choice">Enable Rules to Skip Pages</label>
									<img class="helpmsg" src="images/icons/68_red.png" style="vertical-align: top" title="Enable this option to allow users to jump into the success page or go to any specific page based on their choices. Useful when you have multipage form and need to display different set of pages based on user choices."/>
								</div>
								<div class="ls_box_content" <?php if(empty($logic_page_enable)){ echo 'style="display: none"'; } ?>>
									
									<?php if($form_page_total <= 1){ ?>
									<label style="color: #BD3D20" class="description">Page rules unavailable! <br>You need to add one or more pages into your form.</label>
									<?php } else{ ?>

									<label class="description" for="ls_select_field_rule" style="margin-top: 2px">
										Select Destination Page
									</label>
									<select class="select medium" id="ls_select_page_rule" name="ls_select_page_rule" autocomplete="off">
										<option value=""></option>
										<?php
											foreach ($all_page_labels as $page_id=>$page_title) {
												
												if(!empty($all_logic_pages_id)){
														if(in_array($page_id, $all_logic_pages_id)){
															continue;
														}
												}
												
												echo "<option value=\"{$page_id}\">{$page_title}</option>";
											}
										?>
									</select>
									<select class="select medium" id="ls_select_page_rule_lookup" name="ls_select_page_rule_lookup" autocomplete="off" style="display: none">
										<option value=""></option>
										<?php
											foreach ($all_page_labels as $page_id=>$page_title) {
												echo "<option value=\"{$page_id}\">{$page_title}</option>";
											}
										?>
									</select>
									<ul id="ls_page_rules_group">
										<?php
											if(!empty($logic_pages_array)){

												foreach ($logic_pages_array as $page_id => $value) {
													if(is_numeric($page_id)){
														$page_title = 'Page '.$page_id;
													}else{
														$page_title = ucfirst($page_id).' Page';
													}

													$page_id 	  = 'page'.$page_id;
													$rule_all_any = $value['rule_all_any'];
													
													$jquery_data_code .= "\$(\"#lipagerule_{$page_id}\").data('rule_properties',{\"page_id\": \"{$page_id}\",\"rule_all_any\":\"{$rule_all_any}\"});\n";
												?>

													<li id="lipagerule_<?php echo $page_id; ?>">
														<table width="100%" cellspacing="0">
															<thead>
																<tr>
																	<td>
																		<strong><?php echo $page_title; ?></strong><a class="delete_lipagerule" id="deletelipagerule_<?php echo $page_id; ?>" href="#"><img src="images/icons/52_red_16.png"></a>
																	</td>
																</tr>
															</thead>
															<tbody>
																<tr>
																	<td>
																		<h6>
																			<img src="images/icons/arrow_right_red.png" style="vertical-align: top" />
																			 Go to this page if 
																			<select style="margin-left: 5px;margin-right: 5px" name="pageruleallany_<?php echo $page_id; ?>" id="pageruleallany_<?php echo $page_id; ?>" class="element select rule_all_any">
																				<option value="all" <?php if($rule_all_any == 'all'){ echo 'selected="selected"'; } ?>>all</option>
																				<option value="any" <?php if($rule_all_any == 'any'){ echo 'selected="selected"'; } ?>>any</option>
																			</select> of the following conditions match: 
																		</h6>
																		<ul class="ls_page_rules_conditions">
																			<?php
																				$current_element_conditions = array();
																				$clean_page_id = substr($page_id, 4);
																				$current_element_conditions = $page_logic_conditions_array[$clean_page_id];

																				$i = 1;
																				foreach ($current_element_conditions as $value) {
																					$condition_element_name = $value['element_name'];
																					$rule_condition 		= $value['rule_condition'];
																					$rule_keyword 			= htmlspecialchars($value['rule_keyword'],ENT_QUOTES);

																					$field_element_type = $columns_type[$value['element_name']];
											
																					if($field_element_type == 'matrix'){
																						//if this is matrix field which allow multiselect, change the type to checkbox
																						$temp = array();
																						$temp = explode('_', $condition_element_name);
																						$matrix_element_id = $temp[1];

																						if(in_array($matrix_element_id, $matrix_checkboxes_id_array)){
																							$field_element_type = 'checkbox';
																						}
																					}else if($field_element_type == 'time'){
																						//there are several variants of time fields, we need to make it specific
																						$temp = array();
																						$temp = explode('_', $condition_element_name);
																						$time_element_id = $temp[1];

																						if(!empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
																							$field_element_type = 'time_showsecond24hour';
																						}else if(!empty($time_field_properties[$time_element_id]['showsecond']) && empty($time_field_properties[$time_element_id]['24hour'])){
																							$field_element_type = 'time_showsecond';
																						}else if(empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
																							$field_element_type = 'time_24hour';
																						}

																					}

																					$rule_condition_data = new stdClass();
																					$rule_condition_data->target_page_id 	= $page_id;
																					$rule_condition_data->element_name 		= $condition_element_name;
																					$rule_condition_data->condition 		= $rule_condition;
																					$rule_condition_data->keyword 			= htmlspecialchars_decode($rule_keyword,ENT_QUOTES);

																					$json_rule_condition = json_encode($rule_condition_data);

																					$jquery_data_code .= "\$(\"#lipagerule_{$page_id}_{$i}\").data('rule_condition',{$json_rule_condition});\n";

																					$condition_date_class = '';
																					$time_hour   = '';
																					$time_minute = '';
																					$time_second = '';
																					$time_ampm   = 'AM';
																					
																					if(in_array($field_element_type, array('money','number'))){
																						$condition_text_display = 'display:none';
																						$condition_number_display = '';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																					}else if(in_array($field_element_type, array('date','europe_date'))){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = '';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																						$condition_date_class = 'class="condition_date"';
																					}else if(in_array($field_element_type, array('time','time_showsecond','time_24hour','time_showsecond24hour'))){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = '';
																						$condition_time_display = '';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = 'display:none';
																						$condition_date_class = '';

																						if(!empty($rule_keyword)){
																							$exploded = array();
																							$exploded = explode(':', $rule_keyword);

																							$time_hour   = sprintf("%02s", $exploded[0]);
																							$time_minute = sprintf("%02s", $exploded[1]);
																							$time_second = sprintf("%02s", $exploded[2]);
																							$time_ampm   = strtoupper($exploded[3]); 
																						}
																						
																						//show or hide the second and AM/PM
																						$condition_second_display = '';
																						$condition_ampm_display   = '';
																						
																						if($field_element_type == 'time'){
																							$condition_second_display = 'display:none';
																						}else if($field_element_type == 'time_24hour'){
																							$condition_second_display = 'display:none';
																							$condition_ampm_display   = 'display:none';
																						}else if($field_element_type == 'time_showsecond24hour'){
																							$condition_ampm_display   = 'display:none';
																						} 
																					}else if($field_element_type == 'file'){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																					}else if($field_element_type == 'checkbox'){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = '';
																						$condition_keyword_display = 'display:none';
																					}else{
																						$condition_text_display = '';
																						$condition_number_display = 'display:none';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																					}
																			?>
																			
																				<li id="lipagerule_<?php echo $page_id.'_'.$i; ?>" <?php echo $condition_date_class; ?>>
																					<select id="conditionpage_<?php echo $page_id.'_'.$i; ?>" name="conditionpage_<?php echo $page_id.'_'.$i; ?>" autocomplete="off" class="element select condition_fieldname" style="width: 260px;">
																						<?php
																							foreach ($field_labels as $element_name => $element_label) {
																								
																								if($columns_type[$element_name] == 'signature' || $columns_type[$element_name] == 'file'){
																									continue;
																								}

																								$element_label = htmlspecialchars(strip_tags($element_label));
																								if(strlen($element_label) > 40){
																									$element_label = substr($element_label, 0, 40).'...';
																								}
																								
																								if($condition_element_name == $element_name){
																									$selected_tag = 'selected="selected"';
																								}else{
																									$selected_tag = '';
																								}

																								echo "<option {$selected_tag} value=\"{$element_name}\">{$element_label}</option>\n";
																							}
																						?>
																					</select>
																					<select name="conditiontext_<?php echo $page_id.'_'.$i; ?>" id="conditiontext_<?php echo $page_id.'_'.$i; ?>" class="element select condition_text" style="width: 120px;<?php echo $condition_text_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																						<option <?php if($value['rule_condition'] == 'is_not'){ echo 'selected="selected"'; } ?> value="is_not">Is Not</option>
																						<option <?php if($value['rule_condition'] == 'begins_with'){ echo 'selected="selected"'; } ?> value="begins_with">Begins with</option>
																						<option <?php if($value['rule_condition'] == 'ends_with'){ echo 'selected="selected"'; } ?> value="ends_with">Ends with</option>
																						<option <?php if($value['rule_condition'] == 'contains'){ echo 'selected="selected"'; } ?> value="contains">Contains</option>
																						<option <?php if($value['rule_condition'] == 'not_contain'){ echo 'selected="selected"'; } ?> value="not_contain">Does not contain</option>
																					</select>
																					<select name="conditionnumber_<?php echo $page_id.'_'.$i; ?>" id="conditionnumber_<?php echo $page_id.'_'.$i; ?>" class="element select condition_number" style="width: 120px;<?php echo $condition_number_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																						<option <?php if($value['rule_condition'] == 'less_than'){ echo 'selected="selected"'; } ?> value="less_than">Less than</option>
																						<option <?php if($value['rule_condition'] == 'greater_than'){ echo 'selected="selected"'; } ?> value="greater_than">Greater than</option>
																					</select>
																					<select name="conditiondate_<?php echo $page_id.'_'.$i; ?>" id="conditiondate_<?php echo $page_id.'_'.$i; ?>" class="element select condition_date" style="width: 120px;<?php echo $condition_date_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																						<option <?php if($value['rule_condition'] == 'is_before'){ echo 'selected="selected"'; } ?> value="is_before">Is Before</option>
																						<option <?php if($value['rule_condition'] == 'is_after'){ echo 'selected="selected"'; } ?> value="is_after">Is After</option>
																					</select>
																					<select name="conditioncheckbox_<?php echo $page_id.'_'.$i; ?>" id="conditioncheckbox_<?php echo $page_id.'_'.$i; ?>" class="element select condition_checkbox" style="width: 120px;<?php echo $condition_checkbox_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is_one'){ echo 'selected="selected"'; } ?> value="is_one">Is Checked</option>
																						<option <?php if($value['rule_condition'] == 'is_zero'){ echo 'selected="selected"'; } ?> value="is_zero">Is Empty</option>
																					</select> 
																					<span name="conditiontime_<?php echo $page_id.'_'.$i; ?>" id="conditiontime_<?php echo $page_id.'_'.$i; ?>" class="condition_time" style="<?php echo $condition_time_display; ?>">
																						<input name="conditiontimehour_<?php echo $page_id.'_'.$i; ?>" id="conditiontimehour_<?php echo $page_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_hour; ?>" placeholder="HH"> : 
																						<input name="conditiontimeminute_<?php echo $page_id.'_'.$i; ?>" id="conditiontimeminute_<?php echo $page_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_minute; ?>" placeholder="MM">  
																						<span class="conditiontime_second" style="<?php echo $condition_second_display; ?>"> : <input name="conditiontimesecond_<?php echo $page_id.'_'.$i; ?>" id="conditiontimesecond_<?php echo $page_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_second; ?>" placeholder="SS"> </span>
																						<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_<?php echo $page_id.'_'.$i; ?>" id="conditiontimeampm_<?php echo $page_id.'_'.$i; ?>" style="<?php echo $condition_ampm_display; ?>">
																							<option <?php if($time_ampm == 'AM'){ echo 'selected="selected"'; } ?> value="AM">AM</option>
																							<option <?php if($time_ampm == 'PM'){ echo 'selected="selected"'; } ?> value="PM">PM</option>
																						</select>
																					</span>
																					<input type="text" class="element text condition_keyword" value="<?php echo $rule_keyword; ?>" id="conditionkeyword_<?php echo $page_id.'_'.$i; ?>" name="conditionkeyword_<?php echo $page_id.'_'.$i; ?>" style="<?php echo $condition_keyword_display; ?>">
																					<input type="hidden" value="" class="rule_datepicker" name="datepicker_<?php echo $page_id.'_'.$i; ?>" id="datepicker_<?php echo $page_id.'_'.$i; ?>">
								 		 											<span style="display:none"><img id="datepickimg_<?php echo $page_id.'_'.$i; ?>" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>
																					<a href="#" id="deletecondition_<?php echo $page_id.'_'.$i; ?>" name="deletecondition_<?php echo $page_id.'_'.$i; ?>" class="a_delete_condition"><img src="images/icons/51_red_16.png" /></a>
																				</li>
																			
																			<?php 
																					$i++;
																				} 
																			?>

																			<li class="ls_add_condition">
																				<a href="#" id="addcondition_<?php echo $page_id; ?>" class="a_add_condition"><img src="images/icons/49_red_16.png" /></a>
																			</li>
																		</ul>
																	</td>
																</tr>
															</tbody>
														</table>
													</li>

												
												<?php
													
												}
											}
										?>	
									</ul>

									<?php } ?>
								</div>
							</div>
						</li>
						<li>&nbsp;</li>
						<li>
							<div id="ls_box_email_rules" class="ns_box_main gradient_green">
								<div class="ns_box_title">
									<input type="checkbox" value="1" class="checkbox" id="logic_email_enable" name="logic_email_enable" <?php if(!empty($logic_email_enable)){ echo 'checked="checked"'; } ?>>
									<label for="logic_email_enable" class="choice">Enable Rules to Send Notification Emails</label>
									<img class="helpmsg" src="images/icons/68_green.png" style="vertical-align: top" title="Enable this option to send additional notification emails to any email address based on user choices. You can customize the email content, subject, and from address based on user choices."/>
								</div>
								<div class="ls_box_content" <?php if(empty($logic_email_enable)){ echo 'style="display: none"'; } ?>>									
									<ul id="ls_email_rules_group">
										<?php
											foreach ($logic_emails_array as $rule_id => $value) {
											
												$rule_properties = new stdClass();
												$rule_properties->rule_id 	   			= $rule_id;
												$rule_properties->rule_all_any 			= $value['rule_all_any'];
												$rule_properties->target_email 			= htmlspecialchars_decode($value['target_email'],ENT_QUOTES);
												$rule_properties->template_name 		= $value['template_name'];
												$rule_properties->custom_from_name 		= htmlspecialchars_decode($value['custom_from_name'],ENT_QUOTES);
												$rule_properties->custom_from_email 	= htmlspecialchars_decode($value['custom_from_email'],ENT_QUOTES);
												$rule_properties->custom_replyto_email 	= htmlspecialchars_decode($value['custom_replyto_email'],ENT_QUOTES);
												$rule_properties->custom_subject 		= htmlspecialchars_decode($value['custom_subject'],ENT_QUOTES);
												$rule_properties->custom_content 		= htmlspecialchars_decode($value['custom_content'],ENT_QUOTES);
												$rule_properties->custom_plain_text 	= $value['custom_plain_text'];

												$rule_id = 'email'.$rule_id;
												$json_rule_properties = json_encode($rule_properties);

												$jquery_data_code .= "\$(\"#liemailrule_{$rule_id}\").data('rule_properties',{$json_rule_properties});\n";

												//set Custom Target Email
												$target_email_custom = '';
												if(!empty($target_email_address_list_values)){
													if(!in_array($rule_properties->target_email, $target_email_address_list_values)){
														$target_email_custom = $rule_properties->target_email;
														$rule_properties->target_email 	= 'custom';
													}
												}

												//set Custom From Name
												$custom_from_name_custom = '';
												if(!empty($custom_from_name_list_values)){
													if(!in_array($rule_properties->custom_from_name, $custom_from_name_list_values)){
														$custom_from_name_custom = $rule_properties->custom_from_name;
														$rule_properties->custom_from_name 	= 'custom';
													}
												}

												//set Custom Reply-To Email
												$custom_replyto_email_custom = '';
												if(!empty($custom_replyto_email_list_values)){
													if(!in_array($rule_properties->custom_replyto_email, $custom_replyto_email_list_values)){
														$custom_replyto_email_custom = $rule_properties->custom_replyto_email;
														$rule_properties->custom_replyto_email = 'custom';
													}
												}
										?>

												<li id="liemailrule_<?php echo $rule_id; ?>">
														<table width="100%" cellspacing="0">
															<thead>
																<tr>
																	<td>
																		<strong class="rule_title">Rule #<?php echo $rule_properties->rule_id; ?></strong><a class="delete_liemailrule" id="deleteliemailrule_<?php echo $rule_id; ?>" href="#"><img src="images/icons/52_green_16.png"></a>
																	</td>
																</tr>
															</thead>
															<tbody>
																<tr>
																	<td>
																		<h6>
																			If 
																			<select style="margin-left: 5px;margin-right: 5px" name="emailruleallany_<?php echo $rule_id; ?>" id="emailruleallany_<?php echo $rule_id; ?>" class="element select rule_all_any">
																				<option value="all" <?php if($rule_properties->rule_all_any == 'all'){ echo 'selected="selected"'; } ?>>all</option>
																				<option value="any" <?php if($rule_properties->rule_all_any == 'any'){ echo 'selected="selected"'; } ?>>any</option>
																			</select> of the following conditions match: 
																		</h6>
																		<ul class="ls_email_rules_conditions">
																			
																			<?php
																				$current_element_conditions = array();
																				$clean_rule_id = substr($rule_id, 5);
																				$current_element_conditions = $email_logic_conditions_array[$clean_rule_id];

																				$i = 1;
																				foreach ($current_element_conditions as $value) {
																					$condition_element_name = $value['element_name'];
																					$rule_condition 		= $value['rule_condition'];
																					$rule_keyword 			= htmlspecialchars($value['rule_keyword'],ENT_QUOTES);

																					$field_element_type = $columns_type[$value['element_name']];
											
																					if($field_element_type == 'matrix'){
																						//if this is matrix field which allow multiselect, change the type to checkbox
																						$temp = array();
																						$temp = explode('_', $condition_element_name);
																						$matrix_element_id = $temp[1];

																						if(in_array($matrix_element_id, $matrix_checkboxes_id_array)){
																							$field_element_type = 'checkbox';
																						}
																					}else if($field_element_type == 'time'){
																						//there are several variants of time fields, we need to make it specific
																						$temp = array();
																						$temp = explode('_', $condition_element_name);
																						$time_element_id = $temp[1];

																						if(!empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
																							$field_element_type = 'time_showsecond24hour';
																						}else if(!empty($time_field_properties[$time_element_id]['showsecond']) && empty($time_field_properties[$time_element_id]['24hour'])){
																							$field_element_type = 'time_showsecond';
																						}else if(empty($time_field_properties[$time_element_id]['showsecond']) && !empty($time_field_properties[$time_element_id]['24hour'])){
																							$field_element_type = 'time_24hour';
																						}

																					}

																					$rule_condition_data = new stdClass();
																					$rule_condition_data->target_rule_id 	= $rule_id;
																					$rule_condition_data->element_name 		= $condition_element_name;
																					$rule_condition_data->condition 		= $rule_condition;
																					$rule_condition_data->keyword 			= htmlspecialchars_decode($rule_keyword,ENT_QUOTES);

																					$json_rule_condition = json_encode($rule_condition_data);

																					$jquery_data_code .= "\$(\"#liemailrule_{$rule_id}_{$i}\").data('rule_condition',{$json_rule_condition});\n";

																					$condition_date_class = '';
																					$time_hour   = '';
																					$time_minute = '';
																					$time_second = '';
																					$time_ampm   = 'AM';
																					
																					if(in_array($field_element_type, array('money','number'))){
																						$condition_text_display = 'display:none';
																						$condition_number_display = '';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																					}else if(in_array($field_element_type, array('date','europe_date'))){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = '';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																						$condition_date_class = 'class="condition_date"';
																					}else if(in_array($field_element_type, array('time','time_showsecond','time_24hour','time_showsecond24hour'))){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = '';
																						$condition_time_display = '';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = 'display:none';
																						$condition_date_class = '';

																						if(!empty($rule_keyword)){
																							$exploded = array();
																							$exploded = explode(':', $rule_keyword);

																							$time_hour   = sprintf("%02s", $exploded[0]);
																							$time_minute = sprintf("%02s", $exploded[1]);
																							$time_second = sprintf("%02s", $exploded[2]);
																							$time_ampm   = strtoupper($exploded[3]); 
																						}
																						
																						//show or hide the second and AM/PM
																						$condition_second_display = '';
																						$condition_ampm_display   = '';
																						
																						if($field_element_type == 'time'){
																							$condition_second_display = 'display:none';
																						}else if($field_element_type == 'time_24hour'){
																							$condition_second_display = 'display:none';
																							$condition_ampm_display   = 'display:none';
																						}else if($field_element_type == 'time_showsecond24hour'){
																							$condition_ampm_display   = 'display:none';
																						} 
																					}else if($field_element_type == 'file'){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																					}else if($field_element_type == 'checkbox'){
																						$condition_text_display = 'display:none';
																						$condition_number_display = 'display:none';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = '';
																						$condition_keyword_display = 'display:none';
																					}else{
																						$condition_text_display = '';
																						$condition_number_display = 'display:none';
																						$condition_date_display = 'display:none';
																						$condition_time_display = 'display:none';
																						$condition_checkbox_display = 'display:none';
																						$condition_keyword_display = '';
																					}
																			?>
																			
																				<li id="liemailrule_<?php echo $rule_id.'_'.$i; ?>" <?php echo $condition_date_class; ?>>
																					<select id="conditionemail_<?php echo $rule_id.'_'.$i; ?>" name="conditionemail_<?php echo $rule_id.'_'.$i; ?>" autocomplete="off" class="element select condition_fieldname" style="width: 260px;">
																						<?php
																							foreach ($field_labels as $element_name => $element_label) {
																								
																								if($columns_type[$element_name] == 'signature' || $columns_type[$element_name] == 'file'){
																									continue;
																								}

																								$element_label = htmlspecialchars(strip_tags($element_label));
																								if(strlen($element_label) > 40){
																									$element_label = substr($element_label, 0, 40).'...';
																								}
																								
																								if($condition_element_name == $element_name){
																									$selected_tag = 'selected="selected"';
																								}else{
																									$selected_tag = '';
																								}

																								echo "<option {$selected_tag} value=\"{$element_name}\">{$element_label}</option>\n";
																							}
																						?>
																					</select>
																					<select name="conditiontext_<?php echo $rule_id.'_'.$i; ?>" id="conditiontext_<?php echo $rule_id.'_'.$i; ?>" class="element select condition_text" style="width: 120px;<?php echo $condition_text_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																						<option <?php if($value['rule_condition'] == 'is_not'){ echo 'selected="selected"'; } ?> value="is_not">Is Not</option>
																						<option <?php if($value['rule_condition'] == 'begins_with'){ echo 'selected="selected"'; } ?> value="begins_with">Begins with</option>
																						<option <?php if($value['rule_condition'] == 'ends_with'){ echo 'selected="selected"'; } ?> value="ends_with">Ends with</option>
																						<option <?php if($value['rule_condition'] == 'contains'){ echo 'selected="selected"'; } ?> value="contains">Contains</option>
																						<option <?php if($value['rule_condition'] == 'not_contain'){ echo 'selected="selected"'; } ?> value="not_contain">Does not contain</option>
																					</select>
																					<select name="conditionnumber_<?php echo $rule_id.'_'.$i; ?>" id="conditionnumber_<?php echo $rule_id.'_'.$i; ?>" class="element select condition_number" style="width: 120px;<?php echo $condition_number_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																						<option <?php if($value['rule_condition'] == 'less_than'){ echo 'selected="selected"'; } ?> value="less_than">Less than</option>
																						<option <?php if($value['rule_condition'] == 'greater_than'){ echo 'selected="selected"'; } ?> value="greater_than">Greater than</option>
																					</select>
																					<select name="conditiondate_<?php echo $rule_id.'_'.$i; ?>" id="conditiondate_<?php echo $rule_id.'_'.$i; ?>" class="element select condition_date" style="width: 120px;<?php echo $condition_date_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is'){ echo 'selected="selected"'; } ?> value="is">Is</option>
																						<option <?php if($value['rule_condition'] == 'is_before'){ echo 'selected="selected"'; } ?> value="is_before">Is Before</option>
																						<option <?php if($value['rule_condition'] == 'is_after'){ echo 'selected="selected"'; } ?> value="is_after">Is After</option>
																					</select>
																					<select name="conditioncheckbox_<?php echo $rule_id.'_'.$i; ?>" id="conditioncheckbox_<?php echo $rule_id.'_'.$i; ?>" class="element select condition_checkbox" style="width: 120px;<?php echo $condition_checkbox_display; ?>">
																						<option <?php if($value['rule_condition'] == 'is_one'){ echo 'selected="selected"'; } ?> value="is_one">Is Checked</option>
																						<option <?php if($value['rule_condition'] == 'is_zero'){ echo 'selected="selected"'; } ?> value="is_zero">Is Empty</option>
																					</select> 
																					<span name="conditiontime_<?php echo $rule_id.'_'.$i; ?>" id="conditiontime_<?php echo $rule_id.'_'.$i; ?>" class="condition_time" style="<?php echo $condition_time_display; ?>">
																						<input name="conditiontimehour_<?php echo $rule_id.'_'.$i; ?>" id="conditiontimehour_<?php echo $rule_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_hour; ?>" placeholder="HH"> : 
																						<input name="conditiontimeminute_<?php echo $rule_id.'_'.$i; ?>" id="conditiontimeminute_<?php echo $rule_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_minute; ?>" placeholder="MM">  
																						<span class="conditiontime_second" style="<?php echo $condition_second_display; ?>"> : <input name="conditiontimesecond_<?php echo $rule_id.'_'.$i; ?>" id="conditiontimesecond_<?php echo $rule_id.'_'.$i; ?>" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="<?php echo $time_second; ?>" placeholder="SS"> </span>
																						<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_<?php echo $rule_id.'_'.$i; ?>" id="conditiontimeampm_<?php echo $rule_id.'_'.$i; ?>" style="<?php echo $condition_ampm_display; ?>">
																							<option <?php if($time_ampm == 'AM'){ echo 'selected="selected"'; } ?> value="AM">AM</option>
																							<option <?php if($time_ampm == 'PM'){ echo 'selected="selected"'; } ?> value="PM">PM</option>
																						</select>
																					</span>
																					<input type="text" class="element text condition_keyword" value="<?php echo $rule_keyword; ?>" id="conditionkeyword_<?php echo $rule_id.'_'.$i; ?>" name="conditionkeyword_<?php echo $rule_id.'_'.$i; ?>" style="<?php echo $condition_keyword_display; ?>">
																					<input type="hidden" value="" class="rule_datepicker" name="datepicker_<?php echo $rule_id.'_'.$i; ?>" id="datepicker_<?php echo $rule_id.'_'.$i; ?>">
								 		 											<span style="display:none"><img id="datepickimg_<?php echo $rule_id.'_'.$i; ?>" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>
																					<a href="#" id="deletecondition_<?php echo $rule_id.'_'.$i; ?>" name="deletecondition_<?php echo $rule_id.'_'.$i; ?>" class="a_delete_condition"><img src="images/icons/51_green_16.png" /></a>
																				</li>
																			
																			<?php 
																					$i++;
																				} 
																			?>																			
																			
																			<li class="ls_add_condition">
																				<a href="#" id="addcondition_<?php echo $rule_id; ?>" class="a_add_condition"><img src="images/icons/49_green_16.png" /></a>
																			</li>
																		</ul>
																		<h6>
																			<img src="images/arrows/arrow_right_green.png" style="vertical-align: middle;margin-right: 5px" width="26px"/> Send email to: 
																			
																			<?php  if(!empty($email_fields)){ ?>	
																			<select style="margin-left: 5px" name="targetemail_<?php echo $rule_id; ?>" id="targetemail_<?php echo $rule_id; ?>" class="element select small target_email_dropdown"> 
																				<?php
																					foreach ($target_email_address_list as $data){
																						if($rule_properties->target_email == $data['value']){
																							$selected = 'selected="selected"';
																						}else{
																							$selected = '';
																						}

																						echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
																					}
																				?>
																			</select>
																			<span id="targetemailcustomspan_<?php echo $rule_id; ?>" <?php if($rule_properties->target_email != 'custom'){ echo 'style="display: none"'; } ?>>&#8674; <input id="targetemailcustom_<?php echo $rule_id; ?>" name="targetemailcustom_<?php echo $rule_id; ?>" style="width: 180px" class="element text target_email_custom" value="<?php echo $target_email_custom; ?>" type="text"></span>
																			<?php } else{ ?>
																			<input id="targetemailcustom_<?php echo $rule_id; ?>" name="targetemailcustom_<?php echo $rule_id; ?>" style="width: 180px" class="element text target_email_custom" value="<?php echo $rule_properties->target_email; ?>" type="text">
																			<?php } ?>
																		</h6>
																		<h6 style="margin-bottom: 0px;padding-bottom: 2px">
																			<img src="images/arrows/arrow_right_green.png" style="vertical-align: middle;margin-right: 5px" width="26px"/> Using email template:  
																			<select style="margin-left: 5px;margin-right: 5px" name="customtemplatename_<?php echo $rule_id; ?>" id="customtemplatename_<?php echo $rule_id; ?>" class="element select small template_name">
																				<option value="notification" <?php if($rule_properties->template_name == 'notification'){ echo 'selected="selected"'; } ?>>Notification Email</option>
																				<option value="confirmation" <?php if($rule_properties->template_name == 'confirmation'){ echo 'selected="selected"'; } ?>>Confirmation Email</option>
																				<option value="custom" <?php if($rule_properties->template_name == 'custom'){ echo 'selected="selected"'; } ?>>Custom</option>
																			</select>
																		</h6>
																		<div class="ls_email_rules_custom_template" id="ls_email_custom_template_div_<?php echo $rule_id; ?>" <?php if($rule_properties->template_name != 'custom'){ echo 'style="display: none"'; } ?>>
																			<div class="ls_email_rules_custom_template_head"></div>
																			<div class="ls_email_rules_custom_template_body">
																				<label class="description" for="customfromname_<?php echo $rule_id; ?>">From Name <img class="helpmsg" src="images/icons/68_green_whitebg.png" style="vertical-align: top" title="If your form has 'Name' or 'Single Line Text' field type, it will be available here and you can choose it as the 'From Name' of the email. Or you can set your own custom 'From Name'"/></label>
									
																				<select name="customfromname_<?php echo $rule_id; ?>" id="customfromname_<?php echo $rule_id; ?>" class="element select medium custom_from_name_dropdown"> 
																					<?php
																						foreach ($custom_from_name_list as $data){
																							if($rule_properties->custom_from_name == $data['value']){
																								$selected = 'selected="selected"';
																							}else{
																								$selected = '';
																							}

																							echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
																						}
																					?>			
																				</select>
																				<span id="customfromnamespan_<?php echo $rule_id; ?>" <?php if(empty($custom_from_name_custom)){ echo 'style="display: none"'; } ?>>&#8674; <input id="customfromnameuser_<?php echo $rule_id; ?>" name="customfromnameuser_<?php echo $rule_id; ?>" class="element text custom_from_name_text" style="width: 44%" value="<?php echo $custom_from_name_custom; ?>" type="text"></span>
																				
																				
																				<label class="description" for="customreplytoemail_<?php echo $rule_id; ?>">Reply-To Email <img class="helpmsg" src="images/icons/68_green_whitebg.png" style="vertical-align: top" title="If your form has 'Email' field type, it will be available here and you can choose it as the reply-to address. Or you can set your own custom reply-to address."/></label>
																				<select name="customreplytoemail_<?php echo $rule_id; ?>" id="customreplytoemail_<?php echo $rule_id; ?>" class="element select medium custom_replyto_email_dropdown"> 
																					<?php
																						foreach ($custom_replyto_email_list as $data){
																							if($rule_properties->custom_replyto_email == $data['value']){
																								$selected = 'selected="selected"';
																							}else{
																								$selected = '';
																							}

																							echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
																						}
																					?>			
																				</select>
																				<span id="customreplytoemailspan_<?php echo $rule_id; ?>" <?php if(empty($custom_replyto_email_custom)){ echo 'style="display: none"'; } ?>>&#8674; <input id="customreplytoemailuser_<?php echo $rule_id; ?>" name="customreplytoemailuser_<?php echo $rule_id; ?>" class="element text custom_replyto_email_text" style="width: 44%" value="<?php echo $custom_replyto_email_custom; ?>" type="text"></span>

																				<label class="description" for="customfromemail_<?php echo $rule_id; ?>">From Email <img class="helpmsg" src="images/icons/68_green_whitebg.png" style="vertical-align: top" title="To ensure delivery of your notification emails, we STRONGLY recommend to use email from the same domain as MachForm located.<br/> e.g. no-reply@<?php echo $domain; ?>"/></label>
																				<input id="customfromemail_<?php echo $rule_id; ?>" name="customfromemail_<?php echo $rule_id; ?>" class="element text medium custom_from_email" value="<?php echo $rule_properties->custom_from_email; ?>" type="text">

																				<label class="description" for="customemailsubject_<?php echo $rule_id; ?>">Email Subject</label>
																				<input id="customemailsubject_<?php echo $rule_id; ?>" name="customemailsubject_<?php echo $rule_id; ?>" class="element text large custom_email_subject" value="<?php echo $rule_properties->custom_subject; ?>" type="text">

																				<label class="description" for="customemailcontent_<?php echo $rule_id; ?>">Email Content <img class="helpmsg" src="images/icons/68_green_whitebg.png" style="vertical-align: top" title="This field accept HTML codes."/></label>
																				<textarea class="element textarea medium custom_email_content" name="customemailcontent_<?php echo $rule_id; ?>" id="customemailcontent_<?php echo $rule_id; ?>"><?php echo $rule_properties->custom_content; ?></textarea>

																				<span style="display: block;margin-top: 10px">
																				<input type="checkbox" value="1" class="checkbox custom_plain_text" <?php if(!empty($rule_properties->custom_plain_text)){ echo 'checked="checked"'; } ?> id="customplaintext_<?php echo $rule_id; ?>" name="customplaintext_<?php echo $rule_id; ?>" style="margin-left: 0px">
																				<label for="customplaintext_<?php echo $rule_id; ?>" >Send Email in Plain Text Format</label>
																				</span>

																				<span class="ns_temp_vars"><img style="vertical-align: middle" src="images/icons/70_green_white.png"> You can insert <a href="#" class="tempvar_link">template variables</a> into the email template.</span>
																			</div> 
																		</div>
																	</td>
																</tr>
															</tbody>
														</table>
												</li>

										<?php	
											} //end foreach $logic_emails_array
										?>
																				
									</ul>
									<div id="ls_email_add_rule_div">
										<a id="ls_add_email_rule" href="#">Add Email Rule</a>
										<img style="vertical-align: top;margin-left: 3px" src="images/icons/49_orange_16.png">
									</div>
								</div>
								
							</div>
						</li>
						<!--
						<li>&nbsp;</li>
						<li>
							<div id="ls_box_success_rules" class="ns_box_main gradient_green">
								<div class="ns_box_title">
									<input type="checkbox" value="1" class="checkbox" id="logic_success_enable" name="logic_success_enable">
									<label for="logic_success_enable" class="choice">Enable Rules to Customize Success Page</label>
									<img class="helpmsg" src="images/icons/68_green.png" style="vertical-align: top" title="Enable this option to display custom success page or redirect to any website address after successful form submission. This will override your default success page you create from the Form Builder."/>
								</div>
								
							</div>
						</li>
						-->			
					</ul>
					<input type="hidden" id="form_id" name="form_id" value="<?php echo $form_id; ?>">
					</form>

					<div id="dialog-template-variable" title="Template Variable Lookup" class="buttons" style="display: none"> 
						<form id="dialog-template-variable-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
							<ul>
								<li>
									<div>
										
										<div style="margin: 0px 0 10px 0">
											Template variable &#8674; <span id="tempvar_value">{form_name}</span>
										</div>

										<select class="select full" id="dialog-template-variable-input" style="margin-bottom: 10px" name="dialog-template-variable-input">
											<optgroup label="Form Fields">
											<?php 
												foreach ($columns_label as $element_name => $element_label) {
													echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
												}
											?>
											</optgroup>
											<?php
												if(!empty($complex_field_columns_label)){
													echo "<optgroup label=\"Complex Form Fields (Detailed)\">";
													foreach ($complex_field_columns_label as $element_name => $element_label) {
														echo "<option value=\"{$element_name}\">{$element_label}</option>\n";
													}
													echo "</optgroup>";
												}
											?>
											<optgroup label="Entry Information">
												<option value="entry_no">Entry No.</option>
												<option value="date_created">Date Created</option>
												<option value="ip_address">IP Address</option>
												<option value="form_id">Form ID</option>
												<option value="form_name" selected="selected">Form Name</option>
												<option value="entry_data">Complete Entry</option>
											</optgroup>	
											
											<?php if(!empty($payment_enable_merchant)){ ?>
												<optgroup label="Payment Information">
													<option value="total_amount">Total Amount</option>
													<option value="payment_status">Payment Status</option>
													<option value="payment_id">Payment ID</option>
													<option value="payment_date">Payment Date</option>
													<option value="payment_fullname">Full Name</option>
													<option value="billing_address">Billing Address</option>
													<option value="shipping_address">Shipping Address</option>
												</optgroup>
											<?php } ?>
											
										</select>
										
										<div>
											<div id="tempvar_help_content" style="display: none">
												<h5>What is template variable?</h5>
												<p>A template variable is a special identifier that is automatically replaced with data typed in by a user.</p>

												<h5>How can I use it?</h5>
												<p>Simply copy the variable name (including curly braces) into your email template.</p>

												<h5>Where can I use it?</h5>
												<p>You can insert template variable into Email Subject and Email Content.</p>
											</div>
											<div id="tempvar_help_trigger" style="overflow: auto"><a href="">more info</a></div>
										</div>
									</div> 
								</li>
							</ul>
						</form>
					</div>

				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->
		
<div id="dialog-warning" title="Error Title" class="buttons" style="display: none">
	<img src="images/icons/warning.png" title="Warning" /> 
	<p id="dialog-warning-msg">
		Error
	</p>
</div>
 
<?php
	$footer_data =<<<EOT
<script type="text/javascript">
	$(function(){
		{$jquery_data_code}		
    });
</script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.core.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.pulsate.js"></script>
<script type="text/javascript" src="js/jquery.tools.min.js"></script>
<script type="text/javascript" src="js/datepick/jquery.datepick.js"></script>
<script type="text/javascript" src="js/logic_settings.js"></script>
EOT;

	require('includes/footer.php'); 
?>
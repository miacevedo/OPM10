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

	//handle form submission if there is any
	if(!empty($_POST['form_id'])){

		$notification_settings = mf_sanitize($_POST);
		array_walk($notification_settings, 'mf_trim_value');

		//save settings for 'Send Notification Emails to My Inbox' section
		$form_input['esl_enable'] = (int) $notification_settings['esl_enable'];

		if(empty($notification_settings['esl_email_address'])){
			$form_input['esl_enable'] = 0;
		}

		$form_input['form_email'] = $notification_settings['esl_email_address'];
		
		if($notification_settings['esl_from_name'] == 'custom'){
			$form_input['esl_from_name'] = $notification_settings['esl_from_name_custom'];
		}else{
			$form_input['esl_from_name'] = $notification_settings['esl_from_name'];
		}

		if($notification_settings['esl_from_email_address'] == 'custom'){
			$form_input['esl_from_email_address'] = $notification_settings['esl_from_email_address_custom'];
		}else{
			$form_input['esl_from_email_address'] = $notification_settings['esl_from_email_address'];
		}

		if($notification_settings['esl_replyto_email_address'] == 'custom'){
			$form_input['esl_replyto_email_address'] = $notification_settings['esl_replyto_email_address_custom'];
		}else{
			$form_input['esl_replyto_email_address'] = $notification_settings['esl_replyto_email_address'];
		}

		$form_input['esl_subject'] = $notification_settings['esl_subject'];
		$form_input['esl_content'] = $notification_settings['esl_content'];
		$form_input['esl_plain_text'] = (int) $notification_settings['esl_plain_text'];

		//save settings for 'Send Confirmation to User' section
		$form_input['esr_enable'] = (int) $notification_settings['esr_enable'];
		$form_input['esr_email_address'] = $notification_settings['esr_email_address'];
		
		if($notification_settings['esr_from_name'] == 'custom'){
			$form_input['esr_from_name'] = $notification_settings['esr_from_name_custom'];
		}else{
			$form_input['esr_from_name'] = $notification_settings['esr_from_name'];
		}

		if($notification_settings['esr_from_email_address'] == 'custom'){
			$form_input['esr_from_email_address'] = $notification_settings['esr_from_email_address_custom'];
		}else{
			$form_input['esr_from_email_address'] = $notification_settings['esr_from_email_address'];
		}

		if($notification_settings['esr_replyto_email_address'] == 'custom'){
			$form_input['esr_replyto_email_address'] = $notification_settings['esr_replyto_email_address_custom'];
		}else{
			$form_input['esr_replyto_email_address'] = $notification_settings['esr_replyto_email_address'];
		}

		$form_input['esr_subject'] = $notification_settings['esr_subject'];
		$form_input['esr_content'] = $notification_settings['esr_content'];
		$form_input['esr_plain_text'] = (int) $notification_settings['esr_plain_text'];


		//save settings for 'Send Form Data to Another Website'
		$form_input['webhook_enable'] = (int) $notification_settings['webhook_enable'];
		
		mf_ap_forms_update($form_id,$form_input,$dbh);

		//save into ap_webhook_options table
		$query = "delete from ".MF_TABLE_PREFIX."webhook_options where form_id = ? and rule_id = 0";
		$params = array($form_id);
		mf_do_query($query,$params,$dbh);

		$query = "insert into ".MF_TABLE_PREFIX."webhook_options(
							form_id,
							rule_id,
							webhook_url,
							webhook_method,
							webhook_format,
							webhook_raw_data,
							enable_http_auth,
							http_username,
							http_password,
							enable_custom_http_headers,
							custom_http_headers) 
					 values(?,?,?,?,?,?,?,?,?,?,?)";
		
		$params = array($form_id,
						0,
						$notification_settings['webhook_url'],
						$notification_settings['webhook_method'],
						$notification_settings['webhook_format'],
						$notification_settings['webhook_raw_data'],
						(int) $notification_settings['webhook_enable_http_auth'],
						$notification_settings['webhook_http_username'],
						$notification_settings['webhook_http_password'],
						(int) $notification_settings['webhook_enable_custom_http_headers'],
						$notification_settings['webhook_custom_http_headers']);
		mf_do_query($query,$params,$dbh);

		//save into ap_webhook_parameters table
		if(!empty($notification_settings['webhook_param_names'])){
			//delete previous params
			$query = "delete from ".MF_TABLE_PREFIX."webhook_parameters where form_id = ? and rule_id = 0";
			$params = array($form_id);
			mf_do_query($query,$params,$dbh);
			
			//insert new params
			$webhook_param_names = explode(',', $notification_settings['webhook_param_names']);
			foreach ($webhook_param_names as $value) {
				$param_name  = $notification_settings[$value];
				$value = str_replace('name', 'value', $value);
				$param_value = $notification_settings[$value];

				$query = "insert into ".MF_TABLE_PREFIX."webhook_parameters(form_id,param_name,param_value) values(?,?,?)";
				$params = array($form_id,$param_name,$param_value);
				mf_do_query($query,$params,$dbh);
			}
		}


		$_SESSION['MF_SUCCESS'] = 'Notification settings has been saved.';

		$ssl_suffix = mf_get_ssl_suffix();						
		header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/manage_forms.php?id={$form_id}&hl=1");
		exit;

	}
	
	//get form properties
	$query 	= "select 
					form_name,
					form_email,
					esl_enable,
					esl_from_name,
					esl_from_email_address,
					esl_replyto_email_address,
					esl_subject,
					esl_content,
					esl_plain_text,
					esr_enable,
					esr_email_address,
					esr_from_name,
					esr_from_email_address,
					esr_replyto_email_address,
					esr_subject,
					esr_content,
					esr_plain_text,
					payment_enable_merchant,
					webhook_enable 
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] = mf_trim_max_length($row['form_name'],45);

		$form_name 		= htmlspecialchars($row['form_name']);
		$form_email 	= htmlspecialchars($row['form_email']);
		$esl_from_name 	= htmlspecialchars($row['esl_from_name']);
		$esl_from_email_address		= htmlspecialchars($row['esl_from_email_address']);
		$esl_replyto_email_address	= htmlspecialchars($row['esl_replyto_email_address']);
		$esl_subject 	= htmlspecialchars($row['esl_subject']);
		$esl_content 	= htmlspecialchars($row['esl_content'],ENT_QUOTES);
		$esl_plain_text	= htmlspecialchars($row['esl_plain_text']);
		$esr_email_address = htmlspecialchars($row['esr_email_address']);
		$esr_from_name 	= htmlspecialchars($row['esr_from_name']);
		$esr_from_email_address		= htmlspecialchars($row['esr_from_email_address']);
		$esr_replyto_email_address	= htmlspecialchars($row['esr_replyto_email_address']);
		$esr_subject 	= htmlspecialchars($row['esr_subject']);
		$esr_content 	= htmlspecialchars($row['esr_content'],ENT_QUOTES);
		$esr_plain_text	= htmlspecialchars($row['esr_plain_text']);
		$esl_enable     = (int) $row['esl_enable'];
		$esr_enable     = (int) $row['esr_enable'];
		$payment_enable_merchant = (int) $row['payment_enable_merchant'];
		if($payment_enable_merchant < 1){
			$payment_enable_merchant = 0;
		}

		$webhook_enable = (int) $row['webhook_enable'];
	}

	//get all webhook settings
	$query 	= "select 
					webhook_url,
					webhook_method,
					webhook_format,
					webhook_raw_data,
					enable_http_auth,
					http_username,
					http_password,
					enable_custom_http_headers,
					custom_http_headers
			     from 
			     	 ".MF_TABLE_PREFIX."webhook_options 
			    where 
			    	 form_id = ? and rule_id = 0";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$webhook_url						= htmlspecialchars($row['webhook_url'],ENT_QUOTES);
	$webhook_method 					= strtolower($row['webhook_method']);
	$webhook_format 					= $row['webhook_format'];
	$webhook_raw_data 					= htmlspecialchars($row['webhook_raw_data'],ENT_QUOTES);
	$webhook_enable_http_auth 			= (int) $row['enable_http_auth'];
	$webhook_http_username 				= htmlspecialchars($row['http_username'],ENT_QUOTES);
	$webhook_http_password 				= htmlspecialchars($row['http_password'],ENT_QUOTES);
	$webhook_enable_custom_http_headers = (int) $row['enable_custom_http_headers'];
	$webhook_custom_http_headers 		= htmlspecialchars($row['custom_http_headers'],ENT_QUOTES);

	if(empty($webhook_method)){
		$webhook_method = 'post';
	}
	
	if(empty($webhook_format)){
		$webhook_format = 'key-value';
	}
	
	if(empty($webhook_custom_http_headers)){
		$webhook_custom_http_headers =<<<EOT
{
  "Content-Type": "text/plain",
  "User-Agent": "MachForm Webhook v{$mf_settings['machform_version']}"
} 
EOT;
		$webhook_custom_http_headers = htmlspecialchars($webhook_custom_http_headers,ENT_QUOTES);
	}
	
	//get email fields for this form
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
	
	$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);

	//get "from name" fields for this form, which are name fields and single line text fields
	//get email fields for this form
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

	//prepare the values for 'Send Notification Emails to My Inbox'
	
	//from name
	if(empty($esl_from_name)){
		$esl_from_name = 'MachForm';
	}

	$esl_from_name_list[0]['label'] = 'MachForm';
	$esl_from_name_list[0]['value'] = 'MachForm';
	$esl_from_name_list = array_merge($esl_from_name_list,$name_fields);
		
	$array_max_index = count($esl_from_name_list);

	$esl_from_name_list[$array_max_index]['label'] = '&#8674; Set Custom Name';
	$esl_from_name_list[$array_max_index]['value'] = 'custom';

	$esl_from_name_values = array();
	foreach ($esl_from_name_list as $value) {
		$esl_from_name_values[] = $value['value'];
	}

	if(!in_array($esl_from_name, $esl_from_name_values)){
		$esl_from_name_custom = $esl_from_name;
		$esl_from_name = 'custom';
	}

	//from email address
	if(empty($esl_from_email_address)){
		$esl_from_email_address = $mf_settings['default_from_email'];
	}

	//reply-to email address
	if(empty($esl_replyto_email_address)){
		$esl_replyto_email_address = $mf_settings['default_from_email'];
	}

	$esl_replyto_email_address_list[0]['label'] = "no-reply@{$domain}";
	$esl_replyto_email_address_list[0]['value'] = "no-reply@{$domain}";
	$esl_replyto_email_address_list = array_merge($esl_replyto_email_address_list,$email_fields);
		
	$array_max_index = count($esl_replyto_email_address_list);

	$esl_replyto_email_address_list[$array_max_index]['label'] = '&#8674; Set Custom Address';
	$esl_replyto_email_address_list[$array_max_index]['value'] = 'custom';

	$esl_replyto_email_address_values = array();
	foreach ($esl_replyto_email_address_list as $value) {
		$esl_replyto_email_address_values[] = $value['value'];
	}

	if(!in_array($esl_replyto_email_address, $esl_replyto_email_address_values)){
		$esl_replyto_email_address_custom = $esl_replyto_email_address;
		$esl_replyto_email_address = 'custom';
	}

	//subject
	if(empty($esl_subject)){
		$esl_subject = '{form_name} [#{entry_no}]';
	}

	//content
	if(empty($esl_content)){
		$esl_content = '{entry_data}';
	}


	//prepare the values for 'Send Confirmation Email to User'
	
	//from name
	if(empty($esr_from_name)){
		$esr_from_name = 'MachForm';
	}

	$esr_from_name_list[0]['label'] = 'MachForm';
	$esr_from_name_list[0]['value'] = 'MachForm';
	$esr_from_name_list = array_merge($esr_from_name_list,$name_fields);
		
	$array_max_index = count($esr_from_name_list);

	$esr_from_name_list[$array_max_index]['label'] = '&#8674; Set Custom Name';
	$esr_from_name_list[$array_max_index]['value'] = 'custom';

	$esr_from_name_values = array();
	foreach ($esr_from_name_list as $value) {
		$esr_from_name_values[] = $value['value'];
	}

	if(!in_array($esr_from_name, $esr_from_name_values)){
		$esr_from_name_custom = $esr_from_name;
		$esr_from_name = 'custom';
	}

	//from email address
	if(empty($esr_from_email_address)){
		$esr_from_email_address = $mf_settings['default_from_email'];
	}

	//reply-to email address
	if(empty($esr_replyto_email_address)){
		$esr_replyto_email_address = $mf_settings['default_from_email'];
	}

	$esr_replyto_email_address_list[0]['label'] = "no-reply@{$domain}";
	$esr_replyto_email_address_list[0]['value'] = "no-reply@{$domain}";
	$esr_replyto_email_address_list = array_merge($esr_replyto_email_address_list,$email_fields);
		
	$array_max_index = count($esr_replyto_email_address_list);

	$esr_replyto_email_address_list[$array_max_index]['label'] = '&#8674; Set Custom Address';
	$esr_replyto_email_address_list[$array_max_index]['value'] = 'custom';

	$esr_replyto_email_address_values = array();
	foreach ($esr_replyto_email_address_list as $value) {
		$esr_replyto_email_address_values[] = $value['value'];
	}

	if(!in_array($esr_replyto_email_address, $esr_replyto_email_address_values)){
		$esr_replyto_email_address_custom = $esr_replyto_email_address;
		$esr_replyto_email_address = 'custom';
	}



	//subject
	if(empty($esr_subject)){
		$esr_subject = '{form_name} - Receipt';
	}

	//content
	if(empty($esr_content)){
		$esr_content = '{entry_data}';
	}


	//get all available columns label
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
	
	
	$columns_label = array();
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
		$columns_label['element_'.$element_id] = $element_title;

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

	//get webhook parameters
	//as of now (v4.0) the 'rule_id' will always contain 0
	//later when we implement conditional logic for webhooks, this should contain different rule_id numbers
	$webhook_parameters = array();
	$query = "select param_name,param_value from ".MF_TABLE_PREFIX."webhook_parameters where form_id = ? and rule_id = 0 order by awp_id asc";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		$webhook_parameters[$i]['param_name'] = htmlspecialchars(trim($row['param_name']),ENT_QUOTES);
		$webhook_parameters[$i]['param_value'] = htmlspecialchars($row['param_value'],ENT_QUOTES);
		$i++;
	}

	//if there is no webhook parameters being defined, provide with the default parameters
	if(empty($webhook_parameters)){
		$webhook_parameters[0]['param_name']  = 'FormID';
		$webhook_parameters[0]['param_value'] = '{form_id}';

		$webhook_parameters[1]['param_name']  = 'EntryNumber';
		$webhook_parameters[1]['param_value'] = '{entry_no}';

		$webhook_parameters[2]['param_name']  = 'DateCreated';
		$webhook_parameters[2]['param_value'] = '{date_created}';

		$webhook_parameters[3]['param_name']  = 'IpAddress';
		$webhook_parameters[3]['param_value'] = '{ip_address}';
	}

	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css" rel="stylesheet" />
EOT;

	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post notification_settings">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <img src="images/icons/resultset_next.gif" /> Notification Settings</h2>
							<p>Configure email or web notification options for your form</p>
						</div>	
						<div style="float: right;margin-right: 5px">
								<a href="#" id="button_save_notification" class="bb_button bb_small bb_green">
									<span class="icon-disk" style="margin-right: 5px"></span>Save Settings
								</a>
						</div>
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				<div class="content_body">
					
					<form id="ns_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
					<ul id="ns_main_list">
						<li>
							<div id="ns_box_myinbox" class="ns_box_main gradient_blue">
								<div class="ns_box_title">
									<input type="checkbox" value="1" class="checkbox" id="esl_enable" name="esl_enable" <?php if(!empty($esl_enable)){ echo 'checked="checked"';} ?>>
									<label for="esl_enable" class="choice">Send Notification Emails to My Inbox</label>
									<img class="helpmsg" src="images/icons/68_blue.png" style="vertical-align: top" title="Enable this option to send all successful form submission to your email address (all the data will still be accessible from your machform admin panel as well)."/>
								</div>
								<div class="ns_box_email" <?php if(empty($esl_enable)){ echo 'style="display: none"'; } ?>>
									<label class="description" for="esl_email_address">Your Email Address <img class="helpmsg" src="images/icons/68_blue.png" style="vertical-align: top" title="You can enter multiple email addresses. Simply separate them with commas."/></label>
									<input id="esl_email_address" name="esl_email_address" class="element text medium" value="<?php echo $form_email; ?>" type="text">
								</div>
								<div class="ns_box_more" style="display: none">
									<label class="description" for="esl_from_name">From Name <img class="helpmsg" src="images/icons/68_blue.png" style="vertical-align: top" title="If your form has 'Name' or 'Single Line Text' field type, it will be available here and you can choose it as the 'From Name' of the email. Or you can set your own custom 'From Name'"/></label>
									
									<select name="esl_from_name" id="esl_from_name" class="element select medium"> 
										<?php
											foreach ($esl_from_name_list as $data){
												if($esl_from_name == $data['value']){
													$selected = 'selected="selected"';
												}else{
													$selected = '';
												}

												echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
											}
										?>			
									</select>
									<span id="esl_from_name_custom_span" <?php if(empty($esl_from_name_custom)){ echo 'style="display: none"'; } ?>>&#8674; <input id="esl_from_name_custom" name="esl_from_name_custom" class="element text" style="width: 44%" value="<?php echo $esl_from_name_custom; ?>" type="text"></span>
									
									
									<label class="description" for="esl_replyto_email_address">Reply-To Email <img class="helpmsg" src="images/icons/68_blue.png" style="vertical-align: top" title="If your form has 'Email' field type, it will be available here and you can choose it as the reply-to address. Or you can set your own custom reply-to address."/></label>
									<select name="esl_replyto_email_address" id="esl_replyto_email_address" class="element select medium"> 
										<?php
											foreach ($esl_replyto_email_address_list as $data){
												if($esl_replyto_email_address == $data['value']){
													$selected = 'selected="selected"';
												}else{
													$selected = '';
												}

												echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
											}
										?>			
									</select>
									<span id="esl_replyto_email_address_custom_span" <?php if(empty($esl_replyto_email_address_custom)){ echo 'style="display: none"'; } ?>>&#8674; <input id="esl_replyto_email_address_custom" name="esl_replyto_email_address_custom" class="element text" style="width: 44%" value="<?php echo $esl_replyto_email_address_custom; ?>" type="text"></span>

									<label class="description" for="esl_from_email_address">From Email <img class="helpmsg" src="images/icons/68_blue.png" style="vertical-align: top" title="To ensure delivery of your notification emails, we STRONGLY recommend to use email from the same domain as MachForm located.<br/> e.g. no-reply@<?php echo $domain; ?>"/></label>
									<input id="esl_from_email_address" name="esl_from_email_address" class="element text medium" value="<?php echo $esl_from_email_address; ?>" type="text">

									<label class="description" for="esl_subject">Email Subject</label>
									<input id="esl_subject" name="esl_subject" class="element text large" value="<?php echo $esl_subject; ?>" type="text">

									<label class="description" for="esl_content">Email Content <img class="helpmsg" src="images/icons/68_blue.png" style="vertical-align: top" title="This field accept HTML codes."/></label>
									<textarea class="element textarea medium" name="esl_content" id="esl_content"><?php echo $esl_content; ?></textarea>

									<span style="display: block;margin-top: 10px">
									<input type="checkbox" value="1" class="checkbox" <?php if(!empty($esl_plain_text)){ echo 'checked="checked"'; } ?> id="esl_plain_text" name="esl_plain_text" style="margin-left: 0px">
									<label for="esl_plain_text" >Send Email in Plain Text Format</label>
									</span>

									<span class="ns_temp_vars"><img style="vertical-align: middle" src="images/icons/70_blue.png"> You can insert <a href="#" class="tempvar_link">template variables</a> into the email template.</span>

								</div>
								<div class="ns_box_more_switcher" <?php if(empty($esl_enable)){ echo 'style="display: none"'; } ?>>
									<a id="more_option_myinbox" href="#">more options</a>
									<img id="myinbox_img_arrow" style="vertical-align: top;margin-left: 3px" src="images/icons/38_rightblue_16.png">
								</div>

							</div>
						</li>
						<li>&nbsp;</li>
						<li>
							<div id="ns_box_user_email" class="ns_box_main gradient_red">
								<div class="ns_box_title">
									<input type="checkbox" value="1" class="checkbox" id="esr_enable" name="esr_enable" <?php if(!empty($esr_enable) && !empty($esr_email_address)){ echo 'checked="checked"';} ?>>
									<label for="esr_enable" class="choice">Send Confirmation Email to User</label>
									<img class="helpmsg" src="images/icons/68_red.png" style="vertical-align: top" title="Enable this option to send confirmation email to the user after successful form submission. Your form need to have an email field to use this option."/>
								</div>
								<div class="ns_box_email" <?php if(empty($esr_enable)){ echo 'style="display: none"'; } ?>>
									<?php if(!empty($email_fields)){ ?>
									<label class="description" for="esr_email_address">User Email Address <img class="helpmsg" src="images/icons/68_red.png" style="vertical-align: top" title="Confirmation email will be sent to the email address being entered by the user to this field."/></label>
									<select name="esr_email_address" id="esr_email_address" class="element select medium"> 
										<?php
											foreach ($email_fields as $data){
												if($esr_email_address == $data['value']){
													$selected = 'selected="selected"';
												}else{
													$selected = '';
												}

												echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
											}
										?>			
									</select>
									<?php }else{ ?>
										<label class="description" style="color: #BD3D20">No email field available! <br />You need to add an email field into your form.</label>
									<?php } ?>
								</div>
								<div class="ns_box_more" style="display: none">
									<label class="description" for="esr_from_name">From Name <img class="helpmsg" src="images/icons/68_red.png" style="vertical-align: top" title="If your form has 'Name' or 'Single Line Text' field type, it will be available here and you can choose it as the 'From Name' of the email. Or you can set your own custom 'From Name'"/></label>
									<select name="esr_from_name" id="esr_from_name" class="element select medium"> 
										<?php
											foreach ($esr_from_name_list as $data){
												if($esr_from_name == $data['value']){
													$selected = 'selected="selected"';
												}else{
													$selected = '';
												}

												echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
											}
										?>		
									</select>
									<span id="esr_from_name_custom_span" <?php if(empty($esr_from_name_custom)){ echo 'style="display: none"'; } ?>>&#8674; <input id="esr_from_name_custom" name="esr_from_name_custom" class="element text" style="width: 44%" value="<?php echo $esr_from_name_custom; ?>" type="text"></span>

									<label class="description" for="esr_replyto_email_address">Reply-To Email <img class="helpmsg" src="images/icons/68_red.png" style="vertical-align: top" title="If your form has 'Email' field type, it will be available here and you can choose it as the reply-to address. Or you can set your own custom reply-to address."/></label>
									<select name="esr_replyto_email_address" id="esr_replyto_email_address" class="element select medium"> 
										<?php
											foreach ($esr_replyto_email_address_list as $data){
												if($esr_replyto_email_address == $data['value']){
													$selected = 'selected="selected"';
												}else{
													$selected = '';
												}

												echo "<option value=\"{$data['value']}\" {$selected}>{$data['label']}</option>";
											}
										?>			
									</select>
									<span id="esr_replyto_email_address_custom_span" <?php if(empty($esr_replyto_email_address_custom)){ echo 'style="display: none"'; } ?>>&#8674; <input id="esr_replyto_email_address_custom" name="esr_replyto_email_address_custom" class="element text" style="width: 44%" value="<?php echo $esr_replyto_email_address_custom; ?>" type="text"></span>

									<label class="description" for="esr_from_email_address">From Email <img class="helpmsg" src="images/icons/68_red.png" style="vertical-align: top" title="To ensure delivery of your notification emails, we STRONGLY recommend to use email from the same domain as MachForm located.<br/> e.g. no-reply@<?php echo $domain; ?>"/></label>
									<input id="esr_from_email_address" name="esr_from_email_address" class="element text medium" value="<?php echo $esr_from_email_address; ?>" type="text">

									<label class="description" for="esr_subject">Email Subject</label>
									<input id="esr_subject" name="esr_subject" class="element text large" value="<?php echo $esr_subject; ?>" type="text">

									<label class="description" for="esr_content">Email Content <img class="helpmsg" src="images/icons/68_red.png" style="vertical-align: top" title="This field accept HTML codes."/></label>
									<textarea class="element textarea medium" name="esr_content" id="esl_content"><?php echo $esr_content; ?></textarea>

									<span style="display: block;margin-top: 10px">
									<input type="checkbox" value="1" <?php if(!empty($esr_plain_text)){ echo 'checked="checked"'; } ?> class="checkbox" id="esr_plain_text" name="esr_plain_text" style="margin-left: 0px">
									<label for="esr_plain_text" >Send Email in Plain Text Format</label>
									</span>

									<span class="ns_temp_vars"><img style="vertical-align: middle" src="images/icons/70_red2.png"> You can insert <a href="#" class="tempvar_link">template variables</a> into the email template.</span>
								</div>
								<?php if(!empty($email_fields)){ ?>
								<div class="ns_box_more_switcher" <?php if(empty($esr_enable)){ echo 'style="display: none"'; } ?>>
									<a id="more_option_confirmation_email" href="#">more options</a>
									<img id="confirmation_email_img_arrow" style="vertical-align: top;margin-left: 3px" src="images/icons/38_rightred_16.png">
								</div>
								<?php } ?>
							</div>
						</li>
						<li>&nbsp;</li>
						<li>
							<div id="ns_box_url_notification" class="ns_box_main gradient_green">
								<div class="ns_box_title">
									<input type="checkbox" value="1" class="checkbox" id="webhook_enable" name="webhook_enable" <?php if(!empty($webhook_enable)){ echo 'checked="checked"';} ?>>
									<label for="webhook_enable" class="choice">Send Form Data to Another Website</label>
									<img class="helpmsg" src="images/icons/68_green.png" style="vertical-align: top" title="This is ADVANCED option. You can enable this option to send form data to any other custom URL. Useful to integrate your form with many other web applications, such as Aweber, MailChimp, Salesforce, CampaignMonitor, etc."/>
								</div>
								<div class="ns_box_content" <?php if(!empty($webhook_enable)){ echo 'style="display: block"'; } ?>>
									<label class="description" for="webhook_url">Website URL</label>
									<input id="webhook_url" name="webhook_url" class="element text large" value="<?php echo $webhook_url; ?>" type="text">

									<label class="description" for="webhook_method">HTTP Method</label>
									<select name="webhook_method" id="webhook_method" class="element select medium"> 
										<option <?php if($webhook_method == 'post'){ echo 'selected="selected"'; }; ?> value="post">HTTP POST (recommended)</option>
										<option <?php if($webhook_method == 'get'){ echo 'selected="selected"'; }; ?> value="get">HTTP GET</option>
										<option <?php if($webhook_method == 'put'){ echo 'selected="selected"'; }; ?> value="put">HTTP PUT</option>			
									</select>

									<span style="display: block;margin-top: 15px">
										<input type="checkbox" value="1" class="checkbox" <?php if(!empty($webhook_enable_http_auth)){ echo 'checked="checked"'; } ?> id="webhook_enable_http_auth" name="webhook_enable_http_auth" style="margin-left: 0px">
										<label for="webhook_enable_http_auth" >Use HTTP Authentication</label>
									</span>

									<div id="ns_http_auth_div" <?php if(empty($webhook_enable_http_auth)){ echo 'style="display: none"'; } ?>>
										<label class="description" for="webhook_http_username" style="margin-top: 10px">HTTP User Name</label>
										<input id="webhook_http_username" name="webhook_http_username" class="element text" style="width: 93%" value="<?php echo $webhook_http_username; ?>" type="text">
										
										<label class="description" for="webhook_http_password" style="margin-top: 10px">HTTP Password</label>
										<input id="webhook_http_password" name="webhook_http_password" class="element text" style="width: 93%" value="<?php echo $webhook_http_password; ?>" type="text">
									</div>

									<span style="display: block;margin-top: 10px">
										<input type="checkbox" value="1" class="checkbox" <?php if(!empty($webhook_enable_custom_http_headers)){ echo 'checked="checked"'; } ?> id="webhook_enable_custom_http_headers" name="webhook_enable_custom_http_headers" style="margin-left: 0px">
										<label for="webhook_enable_custom_http_headers">Use Custom HTTP Headers</label>
									</span>

									<div id="ns_http_header_div" <?php if(empty($webhook_enable_custom_http_headers)){ echo 'style="display: none"'; } ?>>
										<label class="description" style="margin-top: 10px" for="webhook_custom_http_headers">HTTP Headers <img class="helpmsg" src="images/icons/68_green.png" style="vertical-align: top" title="A JSON object of all HTTP Headers you need to send."/></label>
										<textarea class="element textarea small" name="webhook_custom_http_headers" id="webhook_custom_http_headers"><?php echo $webhook_custom_http_headers; ?></textarea>
									</div>
									
									<label class="description">Data Format </label>
									<div>
										<span>
											<input id="webhook_data_format_key_value"  name="webhook_format" class="element radio" type="radio" value="key-value" <?php if($webhook_format == 'key-value'){ echo 'checked="checked"'; } ?> />
											<label for="webhook_data_format_key_value">Send Key-Value Pairs</label>
										</span>
										<span style="margin-left: 20px">
											<input id="webhook_data_format_raw"  name="webhook_format" class="element radio" type="radio" value="raw" <?php if($webhook_format == 'raw'){ echo 'checked="checked"'; } ?> />
											<label for="webhook_data_format_raw">Send Raw Data</label>
										</span>
									</div>
									
									<div id="ns_webhook_raw_div" <?php if($webhook_format == 'key-value'){ echo 'style="display: none"'; } ?>>
										<label class="description" style="border-bottom: 1px dashed #97BF6B;padding-bottom: 10px;margin-bottom: 15px">Raw Data <img class="helpmsg" src="images/icons/68_green.png" style="vertical-align: top" title="Enter any content you would like to send here. You can use any data format (e.g. JSON, XML or raw text). Just make sure to set the proper 'Content-Type' HTTP header as well."/></label>
										<textarea class="element textarea large" name="webhook_raw_data" id="webhook_raw_data"><?php echo $webhook_raw_data; ?></textarea>
									</div>
									
									<label id="ns_webhook_parameters_label" <?php if($webhook_format == 'raw'){ echo 'style="display: none"'; } ?> class="description" style="border-bottom: 1px dashed #97BF6B;padding-bottom: 10px">Parameters <img class="helpmsg" src="images/icons/68_green.png" style="vertical-align: top" title="Name -> You can type any parameter name you prefer here. <br/><br/>Value -> Should be the template variable of the field you would like to send. Such as {element_1} or {element_2} etc. You can also enter any static value."/></label>

									<ul id="ns_webhook_parameters" <?php if($webhook_format == 'raw'){ echo 'style="display: none"'; } ?>>
										<li class="ns_url_column_label">
											<div class="ns_param_name">
												<label class="description" for="esl_from_name" style="margin-top: 0px">Name</label>
											</div>
											<div class="ns_param_spacer" style="visibility: hidden">
												&#8674;
											</div>
											<div class="ns_param_value">
												<label class="description" for="esl_from_name" style="margin-top: 0px">Value</label>
											</div>
										</li>
										
										<?php 
											$i=1;
											foreach ($webhook_parameters as $value) { 
										?>	
											<li class="ns_url_params">
												<div class="ns_param_name">
													<input id="webhookname_<?php echo $i; ?>" name="webhookname_<?php echo $i; ?>" class="element text" style="width: 100%" value="<?php echo $value['param_name']; ?>" type="text">
												</div>
												<div class="ns_param_spacer">
													&#8674;
												</div>
												<div class="ns_param_value">
													<input id="webhookvalue_<?php echo $i; ?>" name="webhookvalue_<?php echo $i; ?>" class="element text" style="width: 100%" value="<?php echo $value['param_value']; ?>" type="text">
												</div>
												<div class="ns_param_control">
													<a class="a_delete_webhook_param" name="deletewebhookparam_<?php echo $i; ?>" id="deletewebhookparam_<?php echo $i; ?>" href="#"><img src="images/icons/51_green_16.png"></a>
												</div>
											</li>
										<?php $i++;} ?>
										
										<li class="ns_url_add_param" style="padding-bottom: 0px;text-align: right; border-top: 1px dashed #97BF6B;padding-top: 10px">
											<a class="a_add_condition" id="ns_add_webhook_param" href="#"><img src="images/icons/49_green_16.png"></a>
										</li>
									</ul>
									
									<span class="ns_temp_vars"><img src="images/icons/70_green_white.png" style="vertical-align: middle"> You can insert <a class="tempvar_link" href="#">template variables</a> into parameter values or data.</span>
									
								</div>
							</div>
						</li>		
					</ul>
					<input type="hidden" id="form_id" name="form_id" value="<?php echo $form_id; ?>">
					<input type="hidden" id="webhook_param_names" name="webhook_param_names" value="">
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
					<div id="dialog-warning" title="Error Title" class="buttons" style="display: none">
						<img src="images/icons/warning.png" title="Warning" /> 
						<p id="dialog-warning-msg">
							Error
						</p>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php
	$footer_data =<<<EOT
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
<script type="text/javascript" src="js/notification_settings.js"></script>
EOT;

	require('includes/footer.php'); 
?>
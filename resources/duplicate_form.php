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
	require('includes/users-functions.php');
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	$form_id = (int) $_POST['form_id'];
	$duplicate_success = false;
	
	//check permission, is the user allowed to create new form?
	if(empty($_SESSION['mf_user_privileges']['priv_administer']) && empty($_SESSION['mf_user_privileges']['priv_new_forms'])){
		die("Access Denied. You don't have permission to create new form.");
	}
	
	//get the new form name
	$query 	= "select form_name from `".MF_TABLE_PREFIX."forms` where form_id=?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	$form_name 	 = trim($row['form_name']);
	$form_name .= " Copy";
	
	//get the new form_id
	$query = "select max(form_id)+1 new_form_id from `".MF_TABLE_PREFIX."forms`";
	$params = array();
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	$new_form_id = (int) $row['new_form_id'];
	$new_form_id += rand(100,1000);
	
	//get the columns of ap_forms table
	$query = "show columns from ".MF_TABLE_PREFIX."forms";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'form_name'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode(",",$columns);
	
	//insert the new record into ap_forms table
	$query = "insert into 
							`".MF_TABLE_PREFIX."forms`(form_id,form_name,{$columns_joined}) 
					   select 
							? , ? ,{$columns_joined} 
						from 
							`".MF_TABLE_PREFIX."forms` 
						where 
							form_id = ?";
	$params = array($new_form_id,$form_name,$form_id);
	mf_do_query($query,$params,$dbh);
	
	//create the new table
	$query = "create table `".MF_TABLE_PREFIX."form_{$new_form_id}` like `".MF_TABLE_PREFIX."form_{$form_id}`";
	$params = array();
	mf_do_query($query,$params,$dbh);
	
	//copy ap_form_elements table
	
	//get the columns of ap_form_elements table
	$query = "show columns from ".MF_TABLE_PREFIX."form_elements";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode(",",$columns);
	
	//insert the new record into ap_form_elements table
	$query = "insert into 
							`".MF_TABLE_PREFIX."form_elements`(form_id, {$columns_joined}) 
					   select 
							? , {$columns_joined} 
						from 
							`".MF_TABLE_PREFIX."form_elements` 
						where 
							form_id = ?";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);
	
	//copy ap_element_options table
	
	//get the columns of ap_element_options table
	$query = "show columns from ".MF_TABLE_PREFIX."element_options";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'aeo_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_element_options table
	$query = "insert into 
							`".MF_TABLE_PREFIX."element_options`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."element_options` 
						where 
							form_id = ?";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);
	
	
	
	//copy ap_element_prices table
	
	//get the columns of ap_element_prices table
	$query = "show columns from ".MF_TABLE_PREFIX."element_prices";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'aep_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_element_prices table
	$query = "insert into 
							`".MF_TABLE_PREFIX."element_prices`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."element_prices` 
						where 
							form_id = ?";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);
	

	//copy ap_field_logic_elements table
	
	//get the columns of ap_logic_elements table
	$query = "show columns from ".MF_TABLE_PREFIX."field_logic_elements";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_field_logic_elements table
	$query = "insert into 
							`".MF_TABLE_PREFIX."field_logic_elements`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."field_logic_elements` 
						where 
							form_id = ?";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//copy ap_field_logic_conditions table
	
	//get the columns of ap_field_logic_conditions table
	$query = "show columns from ".MF_TABLE_PREFIX."field_logic_conditions";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'alc_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_field_logic_conditions table
	$query = "insert into 
							`".MF_TABLE_PREFIX."field_logic_conditions`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."field_logic_conditions` 
						where 
							form_id = ?
				    order by 
				    		alc_id asc";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//copy ap_page_logic table
	
	//get the columns of ap_page_logic table
	$query = "show columns from ".MF_TABLE_PREFIX."page_logic";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_page_logic table
	$query = "insert into 
							`".MF_TABLE_PREFIX."page_logic`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."page_logic` 
						where 
							form_id = ?";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//copy ap_page_logic_conditions table
	
	//get the columns of ap_page_logic_conditions table
	$query = "show columns from ".MF_TABLE_PREFIX."page_logic_conditions";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'apc_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_page_logic_conditions table
	$query = "insert into 
							`".MF_TABLE_PREFIX."page_logic_conditions`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."page_logic_conditions` 
						where 
							form_id = ? order by apc_id asc";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);


	//copy ap_field_logic_conditions table
	
	//get the columns of ap_field_logic_conditions table
	$query = "show columns from ".MF_TABLE_PREFIX."field_logic_conditions";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'alc_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_field_logic_conditions table
	$query = "insert into 
							`".MF_TABLE_PREFIX."field_logic_conditions`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."field_logic_conditions` 
						where 
							form_id = ?
				    order by 
				    		alc_id asc";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//copy ap_email_logic table
	
	//get the columns of ap_email_logic table
	$query = "show columns from ".MF_TABLE_PREFIX."email_logic";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_page_logic table
	$query = "insert into 
							`".MF_TABLE_PREFIX."email_logic`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."email_logic` 
						where 
							form_id = ?";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//copy ap_email_logic_conditions table
	
	//get the columns of ap_email_logic_conditions table
	$query = "show columns from ".MF_TABLE_PREFIX."email_logic_conditions";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'aec_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_email_logic_conditions table
	$query = "insert into 
							`".MF_TABLE_PREFIX."email_logic_conditions`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."email_logic_conditions` 
						where 
							form_id = ? order by aec_id asc";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//copy ap_webhook_options table
	
	//get the columns of ap_webhook_options table
	$query = "show columns from ".MF_TABLE_PREFIX."webhook_options";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'awo_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_webhook_options table
	$query = "insert into 
							`".MF_TABLE_PREFIX."webhook_options`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."webhook_options` 
						where 
							form_id = ? order by awo_id asc";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//copy ap_webhook_parameters table
	
	//get the columns of ap_webhook_parameters table
	$query = "show columns from ".MF_TABLE_PREFIX."webhook_parameters";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'awp_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_webhook_parameters table
	$query = "insert into 
							`".MF_TABLE_PREFIX."webhook_parameters`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."webhook_parameters` 
						where 
							form_id = ? order by awp_id asc";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//copy ap_reports table
	
	//insert new report_access_key into ap_reports
	$new_report_access_key = $new_form_id.'x'.substr(strtolower(md5(uniqid(rand(), true))),0,10);

	$query = "insert into `".MF_TABLE_PREFIX."reports`(form_id, report_access_key) values(?,?)";
	$params = array($new_form_id,$new_report_access_key);
	mf_do_query($query,$params,$dbh);

	//copy ap_report_elements table
	
	//get the columns of ap_report_elements table
	$query = "show columns from ".MF_TABLE_PREFIX."report_elements";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_report_elements table
	$query = "insert into 
							`".MF_TABLE_PREFIX."report_elements`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."report_elements` 
						where 
							form_id = ?";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//update access_key on each ap_report_elements record
	$old_element_access_key_array = array();
	$query = "select access_key from ".MF_TABLE_PREFIX."report_elements where form_id = ?";
	$params = array($new_form_id);

	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		$old_element_access_key_array[] = $row['access_key'];
	}

	if(!empty($old_element_access_key_array)){
		foreach($old_element_access_key_array as $access_key){
			$new_access_key = str_replace($form_id.'x', $new_form_id.'x', $access_key);

			$query  = "update ".MF_TABLE_PREFIX."report_elements set access_key = ? where form_id = ? and access_key = ?";
			$params = array($new_access_key, $new_form_id, $access_key);
			mf_do_query($query,$params,$dbh);
		}
	}


	//copy ap_report_filters table
	
	//get the columns of ap_report_filters table
	$query = "show columns from ".MF_TABLE_PREFIX."report_filters";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'arf_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_report_filters table
	$query = "insert into 
							`".MF_TABLE_PREFIX."report_filters`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."report_filters` 
						where 
							form_id = ? order by arf_id asc";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);
	
	//copy ap_grid_columns table
	
	//get the columns of ap_grid_columns table
	$query = "show columns from ".MF_TABLE_PREFIX."grid_columns";
	$params = array();
	
	$columns = array();
	$sth = mf_do_query($query,$params,$dbh);
	while($row = mf_do_fetch_result($sth)){
		if($row['Field'] == 'form_id' || $row['Field'] == 'agc_id'){
			continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
		}
		$columns[] = $row['Field'];
	}
	
	$columns_joined = implode("`,`",$columns);
	
	//insert the new record into ap_report_filters table
	$query = "insert into 
							`".MF_TABLE_PREFIX."grid_columns`(form_id, `{$columns_joined}`) 
					   select 
							? , `{$columns_joined}` 
						from 
							`".MF_TABLE_PREFIX."grid_columns` 
						where 
							form_id = ? order by agc_id asc";
	$params = array($new_form_id,$form_id);
	mf_do_query($query,$params,$dbh);

	//copy review table, if there is any
	$review_table_exist = true;
	try {
		  $dbh->query("select count(*) from `".MF_TABLE_PREFIX."form_{$form_id}_review`");
	} catch(PDOException $e) {
		  $review_table_exist  = false;
	}
	
	if($review_table_exist){
		$query = "CREATE TABLE `".MF_TABLE_PREFIX."form_{$new_form_id}_review` like `".MF_TABLE_PREFIX."form_{$form_id}_review`";
		mf_do_query($query,$params,$dbh);
	}
	
	
	//create data folder for this form
	if(is_writable($mf_settings['data_dir'])){
			
		$old_mask = umask(0);
		mkdir($mf_settings['data_dir']."/form_{$new_form_id}",0777);
		mkdir($mf_settings['data_dir']."/form_{$new_form_id}/css",0777);
		if($mf_settings['data_dir'] != $mf_settings['upload_dir']){
			@mkdir($mf_settings['upload_dir']."/form_{$new_form_id}",0777);
		}
		mkdir($mf_settings['upload_dir']."/form_{$new_form_id}/files",0777);
			
		//copy css file	
		copy($mf_settings['data_dir']."/form_{$form_id}/css/view.css",$mf_settings['data_dir']."/form_{$new_form_id}/css/view.css");
			
		umask($old_mask);
	}

	//insert into permissions table
	$query = "insert into ".MF_TABLE_PREFIX."permissions(form_id,user_id,edit_form,edit_entries,view_entries) values(?,?,1,1,1)";
	$params = array($new_form_id,$_SESSION['mf_user_id']);
	mf_do_query($query,$params,$dbh);
	
	
	
	$duplicate_success = true;

	$response_data = new stdClass();
	
	if($duplicate_success){
		$response_data->status    	= "ok";
	}else{
		$response_data->status    	= "error";
	}
	
	$response_data->form_id 	= $new_form_id;
	$response_json = json_encode($response_data);
	
	$_SESSION['MF_SUCCESS'] = 'Your form has been duplicated.';
	
	echo $response_json;
	
?>
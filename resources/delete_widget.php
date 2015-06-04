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
	
	$form_id 	= (int) trim($_POST['form_id']);
	$chart_id   = (int) trim($_POST['chart_id']);

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			die("You don't have permission to edit this form.");
		}
	}
	
	//delete permanently if true_delete is turned on	
	if(MF_CONF_TRUE_DELETE === true){
		$query = "DELETE FROM ".MF_TABLE_PREFIX."report_elements where form_id = ? and chart_id = ?";
		
		$params = array($form_id,$chart_id);
		mf_do_query($query,$params,$dbh);
	}else{
		//set the status on ap_form_elements table to 0
		$query = "update `".MF_TABLE_PREFIX."report_elements` set chart_status = 0 where form_id = ? and chart_id = ?";
		$params = array($form_id,$chart_id);
		mf_do_query($query,$params,$dbh);
		
		
	}	
	
	$response_data = new stdClass();
	
	$response_data->status    	= "ok";
	$response_data->chart_id 	= $chart_id;
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>
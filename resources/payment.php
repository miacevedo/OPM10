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
	
	require('includes/language.php');
	require('includes/common-validator.php');
	require('includes/view-functions.php');
	require('includes/theme-functions.php');
	require('includes/post-functions.php');
	require('includes/entry-functions.php');
	require('lib/swift-mailer/swift_required.php');
	require('lib/HttpClient.class.php');
	require('hooks/custom_hooks.php');
	
	$dbh 		  = mf_connect_db();
	$form_id 	  = (int) trim($_REQUEST['id']);
	$paid_form_id = (int) trim($_POST['form_id_redirect']);


	if(!empty($paid_form_id) && $_SESSION['mf_payment_completed'][$paid_form_id] === true){
		//when payment succeeded, $paid_form_id should contain the form id number
		$form_properties = mf_get_form_properties($dbh,$paid_form_id,array('form_redirect_enable','form_redirect','form_review','form_page_total','payment_delay_notifications'));
		
		//process any delayed notifications
		if(!empty($form_properties['payment_delay_notifications'])){
			mf_process_delayed_notifications($dbh,$paid_form_id,$_SESSION['mf_payment_record_id'][$paid_form_id]);
		}
		

		//redirect to the default success page or the custom redirect URL being set on form properties
		if(!empty($form_properties['form_redirect_enable']) && !empty($form_properties['form_redirect'])){
			
			//parse redirect URL for any template variables first
			$form_properties['form_redirect'] = mf_parse_template_variables($dbh,$paid_form_id,$_SESSION['mf_payment_record_id'][$paid_form_id],$form_properties['form_redirect']);

			echo "<script type=\"text/javascript\">top.location.replace('{$form_properties['form_redirect']}')</script>";
			exit;
		}else{
			$ssl_suffix = mf_get_ssl_suffix();
			
			header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/view.php?id={$paid_form_id}&done=1");
			exit;
		}
	}else{
		//display payment form
		if(empty($form_id)){
			die('ID required.');
		}else{
			$record_id = $_SESSION['mf_payment_record_id'][$form_id];	
			$markup    = mf_display_form_payment($dbh,$form_id,$record_id);
				
			header("Content-Type: text/html; charset=UTF-8");
			echo $markup;
		}
	}
?>
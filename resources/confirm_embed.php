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
		
	//get data from database
	$dbh 		= mf_connect_db();
	$ssl_suffix = mf_get_ssl_suffix();
	
	$form_id    = (int) trim($_REQUEST['id']);
	
	if(!empty($_POST['review_submit']) || !empty($_POST['review_submit_x'])){ //if form submitted	
		
		//commit data from review table to actual table
		//however, we need to check if this form has payment enabled or not

		//if the form doesn't have any payment enabled, continue with commit and redirect to success page
		$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','payment_delay_notifications','payment_merchant_type'));
		
		if($form_properties['payment_enable_merchant'] != 1){
			$record_id 	   = $_SESSION['review_id'];
			$commit_result = mf_commit_form_review($dbh,$form_id,$record_id);
			
			unset($_SESSION['review_id']);
			
			if(empty($commit_result['form_redirect'])){		
				header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
				exit;
			}else{
				echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
				exit;
			}
		}else{
			//if the form has payment enabled, continue commit and redirect to payment page
			$record_id 	    = $_SESSION['review_id'];
			$commit_options = array();
			
			//delay notifications only available on some merchants 
			if(!empty($form_properties['payment_delay_notifications']) && in_array($form_properties['payment_merchant_type'], array('stripe','paypal_standard','authorizenet','paypal_rest','braintree'))){	
				$commit_options['send_notification'] = false;
			}

			$commit_result = mf_commit_form_review($dbh,$form_id,$record_id,$commit_options);

			unset($_SESSION['review_id']);
			
			if(in_array($form_properties['payment_merchant_type'], array('stripe','authorizenet','paypal_rest','braintree'))){
				//allow access to payment page
				$_SESSION['mf_form_payment_access'][$form_id] = true;
				$_SESSION['mf_payment_record_id'][$form_id] = $commit_result['record_insert_id'];

				header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/payment_embed.php?id={$form_id}");
				exit;
			}else if($form_properties['payment_merchant_type'] == 'paypal_standard'){
				if(empty($commit_result['form_redirect'])){
					header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
					exit;
				}else{
					echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
					exit;
				}
			}else if($form_properties['payment_merchant_type'] == 'check'){
				//redirect to either success page or custom redirect URL
				if(empty($commit_result['form_redirect'])){				
					header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?id={$form_id}&done=1");
					exit;
				}else{
					echo "<script type=\"text/javascript\">top.location.replace('{$commit_result['form_redirect']}')</script>";
					exit;
				}
			}
		}
		
	}elseif (!empty($_POST['review_back']) || !empty($_POST['review_back_x'])){ 
		//go back to form
		$origin_page_num = (int) $_POST['mf_page_from'];
		header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/embed.php?id={$form_id}&mf_page={$origin_page_num}");
		exit;
	}else{
				
		if(empty($form_id)){
			die('ID required.');
		}
		
		if(!empty($_GET['done']) && !empty($_SESSION['mf_form_completed'][$form_id])){
			$form_params = array();
			$form_params['integration_method'] = 'iframe';
			
			$markup = mf_display_success($dbh,$form_id,$form_params);
		}else{
			if(empty($_SESSION['review_id'])){
				die("Your session has been expired. Please <a href='embed.php?id={$form_id}'>click here</a> to start again.");
			}else{
				$record_id = $_SESSION['review_id'];
			}
			
			$from_page_num = (int) $_GET['mf_page_from'];
			if(empty($from_page_num)){
				$form_page_num = 1;
			}
			
			$form_params = array();
			$form_params['integration_method'] = 'iframe';

			$markup = mf_display_form_review($dbh,$form_id,$record_id,$from_page_num,$form_params);
		}
	}
	
	header("Content-Type: text/html; charset=UTF-8");
	echo $markup;
	
?>

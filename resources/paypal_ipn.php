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
	require('includes/language.php');

	require('includes/helper-functions.php');
	require('includes/filter-functions.php');
	require('includes/post-functions.php');
	require('includes/entry-functions.php');
	require('lib/swift-mailer/swift_required.php');
	require('lib/HttpClient.class.php');
	require('hooks/custom_hooks.php');
	require('lib/ipnlistener/ipnlistener.php');

	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);


	//prepare log file
	$use_debug_mode = false; //set to 'true' to display verbose log mode into data/ipn_error_log.php file
	

	//don't modify below codes ------
	$log_error 	= false;
	$log_file_path = $mf_settings['upload_dir']."/ipn_error_log.php";
	if(file_exists($log_file_path) !== true){
		if(is_writable($mf_settings['upload_dir'])){
			file_put_contents($log_file_path,' ');
			$log_error = true;
		}
	}else{
		$log_error = true;
	}
	
	if($log_error){
		ini_set('log_errors', true);
		ini_set('error_log', $log_file_path);
	}


	$temp_exploded = explode('_', $_POST['custom']); //the "custom" variable from PayPal format: xx_yy_zzzzzzzz (xx: form_id, yy: entry_id, zzz: unix_timestamp of the date_created field)
	$form_id  = (int) $temp_exploded[0];

	if(!empty($form_id)){
		$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_paypal_enable_test_mode'));
	}

	//start the listener
	$listener = new IpnListener();
	
	if(function_exists('curl_init')){
		$listener->use_curl = true;
	}else{
		$listener->use_curl = false;
	}

	if(!empty($form_properties['payment_paypal_enable_test_mode'])){
		$listener->use_sandbox = true;
	}else{
		$listener->use_sandbox = false;
	}

	try {
	    $listener->requirePostMethod();
	    $verified = $listener->processIpn();
	} catch (Exception $e) {
	    error_log($e->getMessage());
	    exit;
	}

	if($use_debug_mode){
		error_log('IPN RECEIVED');
		error_log($listener->getTextReport());
	}
	
	
	//valid IPN, verify the data further
	if($verified === true){

		if($use_debug_mode){
			error_log('Valid IPN');
		}

		$error_message = '';

		//make sure the payment status is completed
		if($_POST['payment_status'] != 'Completed'){
			exit; //ignore any non completed IPN
		}

		//parse the "custom" variable and make sure it's a valid entry within the database 
		$exploded = explode('_', $_POST['custom']); //the "custom" variable from PayPal format: xx_yy_zzzzzzzz (xx: form_id, yy: entry_id, zzz: unix_timestamp of the date_created field)
		$form_id  		 = (int) $exploded[0];
		$entry_id 		 = $exploded[1];
		$entry_timestamp = $exploded[2];

		$query = "select count(`id`) record_exist from ".MF_TABLE_PREFIX."form_{$form_id} where unix_timestamp(date_created) = ? and `id` = ? and `status` = 1";
		$params = array($entry_timestamp,$entry_id);

		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		if(empty($row['record_exist'])){
			$error_message .= "Invalid custom parameter: {$_POST['custom']}";
		}

		$query 	= "select 
						 ifnull(payment_paypal_email,'') payment_paypal_email,
						 payment_currency,
						 payment_price_type,
						 payment_price_amount,
						 payment_enable_tax,
						 payment_tax_rate,
						 payment_enable_discount,
						 payment_discount_type,
						 payment_discount_amount,
						 payment_discount_element_id,
						 payment_delay_notifications
				     from 
				     	 `".MF_TABLE_PREFIX."forms` 
				    where 
				    	 form_id=?";
		
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$payment_paypal_email = strtolower($row['payment_paypal_email']);
		$payment_currency 	  = $row['payment_currency'];
		$payment_price_type   = $row['payment_price_type'];
		$payment_price_amount = (float) $row['payment_price_amount'];

		$payment_enable_tax = (int) $row['payment_enable_tax'];
		$payment_tax_rate 	= (float) $row['payment_tax_rate'];

		$payment_enable_discount = (int) $row['payment_enable_discount'];
		$payment_discount_type 	 = $row['payment_discount_type'];
		$payment_discount_amount = (float) $row['payment_discount_amount'];
		$payment_discount_element_id = (int) $row['payment_discount_element_id'];

		$payment_delay_notifications = (int) $row['payment_delay_notifications'];

		$is_discount_applicable = false;

		//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
		if(!empty($payment_enable_discount)){
			$query = "select element_{$payment_discount_element_id} coupon_element from ".MF_TABLE_PREFIX."form_{$form_id} where `id` = ? and `status` = 1";
			$params = array($entry_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			if(!empty($row['coupon_element'])){
				$is_discount_applicable = true;
			}
		}

		//make sure the currency match
		if(strtolower($payment_currency) != strtolower($_POST['mc_currency'])){
			$error_message .= "PayPal currency does not match. Current: {$payment_currency}: - mc_currency: {$_POST['mc_currency']}";
		}
		
		//make sure the amount paid match or larger
		if($payment_price_type == 'variable'){
			$payment_amount = (double) mf_get_payment_total($dbh,$form_id,$entry_id,0,'live');
		}else if($payment_price_type == 'fixed'){
			$payment_amount = (double) $payment_price_amount;
		}

		//calculate discount if applicable
		if($is_discount_applicable){
			$payment_calculated_discount = 0;

			if($payment_discount_type == 'percent_off'){
				//the discount is percentage
				$payment_calculated_discount = ($payment_discount_amount / 100) * $payment_amount;
				$payment_calculated_discount = round($payment_calculated_discount,2); //round to 2 digits decimal
			}else{
				//the discount is fixed amount
				$payment_calculated_discount = round($payment_discount_amount,2); //round to 2 digits decimal
			}

			$payment_amount -= $payment_calculated_discount;
			
		}

		//calculate tax if enabled
		if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
			$payment_tax_amount = ($payment_tax_rate / 100) * $payment_amount;
			$payment_tax_amount = round($payment_tax_amount,2); //round to 2 digits decimal
			$payment_amount += $payment_tax_amount;
		}

		
		$gross_payment = (double) $_POST['mc_gross'];
		if(abs($gross_payment - $payment_amount) > 1) {
			$error_message .= "Gross amount does not match. Amount: {$payment_amount} - mc_gross: {$gross_payment}";	
		}
		
		//if there is any error, log and exit
		if(!empty($error_message)){
			error_log($error_message);
			error_log($listener->getTextReport());
			exit;
		}else{

			if($use_debug_mode){
				error_log('Verification completed. Update/insert into table');
			}

			//otherwise update/insert into ap_form_payments table with the completed status
			$query = "select count(afp_id) record_exist from ".MF_TABLE_PREFIX."form_payments where form_id = ? and record_id = ? and `status` = 1";
			$params = array($form_id,$entry_id);
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);
			
			$payment_status = 'paid';

			if(!empty($row['record_exist'])){
				if($use_debug_mode){
					error_log('Updating form_payments table');
				}

				//do update to ap_form_payments table
				$query = "update ".MF_TABLE_PREFIX."form_payments set payment_status = ? where form_id = ? and record_id = ? and `status` = 1";
				$params = array($payment_status,$form_id,$entry_id);
				mf_do_query($query,$params,$dbh);

				if($use_debug_mode){
					error_log('Done updating form_payments table');
				}

			}else{

				if($use_debug_mode){
					error_log('Inserting to form_payments table');
				}
				//do insert to ap_form_payments table
				//insert into ap_form_payments table
				$query = "INSERT INTO `".MF_TABLE_PREFIX."form_payments`(
										`form_id`, 
										`record_id`, 
										`payment_id`, 
										`date_created`, 
										`payment_date`, 
										`payment_status`, 
										`payment_fullname`, 
										`payment_amount`, 
										`payment_currency`, 
										`payment_test_mode`,
										`payment_merchant_type`, 
										`status`
										) 
								VALUES (
										:form_id, 
										:record_id, 
										:payment_id, 
										:date_created, 
										:payment_date, 
										:payment_status, 
										:payment_fullname, 
										:payment_amount, 
										:payment_currency, 
										:payment_test_mode,
										:payment_merchant_type, 
										:status 
										)";		

				
				$params = array();
				$params[':form_id'] 		  	= $form_id;
				$params[':record_id'] 			= $entry_id;
				$params[':payment_id'] 			= $_POST['txn_id'];
				$params[':date_created'] 		= date("Y-m-d H:i:s");
				$params[':payment_date'] 		= date("Y-m-d H:i:s",strtotime($_POST['payment_date']));
				$params[':payment_status'] 		= 'paid';
				$params[':payment_fullname']  	= trim($_POST['first_name'].' '.$_POST['last_name']);
				$params[':payment_amount'] 	  	= $_POST['mc_gross'];
				$params[':payment_currency']  	= $payment_currency;
				
				if($use_paypal_sandbox){
					$params[':payment_test_mode'] 	= 1;
				}else{
					$params[':payment_test_mode'] 	= 0;
				}

				$params[':payment_merchant_type'] = 'paypal_standard';
				$params[':status'] 			  	  = 1;
				
				mf_do_query($query,$params,$dbh);

				if($use_debug_mode){
					error_log('Done inserting to form_payments table');
				}

				//send any delayed notifications
				if(!empty($payment_delay_notifications)){
					if($use_debug_mode){
						error_log('Sending delayed notifications..');
					}

					mf_process_delayed_notifications($dbh,$form_id,$entry_id);
				}
			}

		} //end update/insert into ap_form_payments

	}else{
		if($use_debug_mode){
			error_log('Invalid IPN');
		}
	}

?>
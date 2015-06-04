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
	require('includes/filter-functions.php');
	require('includes/post-functions.php');
	require('lib/stripe/Stripe.php');
	
	$form_id 			= (int) trim($_POST['form_id']);
	$token 				= trim($_POST['token']);
	$payment_record_id 	= $_SESSION['mf_payment_record_id'][$form_id];
	$payment_data 		= mf_sanitize($_POST['payment_properties']);

	$payment_success  = false;
	$payment_message = '';
	
	if(empty($form_id) || empty($payment_record_id) || empty($token)){
		$response_data = new stdClass();
		$response_data->status    	= "error";
		$response_data->message 	= "Error. Your session has been expired. Please start the form again.";
		
		$response_json = json_encode($response_data);
		echo $response_json;
		
		exit;
	}

	$dbh = mf_connect_db();
	
	//get form properties data
	$query 	= "select 
					form_review,
					form_page_total,
					payment_enable_merchant,
					payment_merchant_type,
					payment_currency,
					payment_price_type,
					payment_price_name,
					payment_price_amount,
					payment_ask_billing,
					payment_ask_shipping,
					payment_stripe_live_secret_key,
					payment_stripe_test_secret_key,
					payment_stripe_enable_test_mode,
					payment_enable_recurring,
					payment_recurring_cycle,
					payment_recurring_unit,
					payment_enable_trial,
					payment_trial_period,
					payment_trial_unit,
					payment_trial_amount,
					payment_delay_notifications,
					payment_enable_tax,
					payment_tax_rate,
					payment_enable_discount,
					payment_discount_type,
					payment_discount_amount,
					payment_discount_element_id 
				from 
				    ".MF_TABLE_PREFIX."forms 
			   where 
				    form_id=?";
	$params = array($form_id);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
		
	$form_payment_title			 = $mf_lang['form_payment_title'];
	$form_payment_description	 = $mf_lang['form_payment_description'];

	$form_review  	 		= (int) $row['form_review'];
	$form_page_total 		= (int) $row['form_page_total'];
		
	$payment_enable_merchant 	 = (int) $row['payment_enable_merchant'];
	if($payment_enable_merchant < 1){
		$payment_enable_merchant = 0;
	}
	
	$payment_enable_tax 		 = (int) $row['payment_enable_tax'];
	$payment_tax_rate 			 = (float) $row['payment_tax_rate'];

	$payment_currency 	   		 = strtolower($row['payment_currency']);
	$payment_price_type 	     = $row['payment_price_type'];
	$payment_price_amount    	 = $row['payment_price_amount'];
	$payment_ask_billing 	 	 = (int) $row['payment_ask_billing'];
	$payment_ask_shipping 	 	 = (int) $row['payment_ask_shipping'];
	$payment_merchant_type		 = $row['payment_merchant_type'];
	
	$payment_stripe_enable_test_mode = (int) $row['payment_stripe_enable_test_mode'];
	$payment_stripe_live_secret_key	 = trim($row['payment_stripe_live_secret_key']);
	$payment_stripe_test_secret_key	 = trim($row['payment_stripe_test_secret_key']);
	
	$payment_price_type   = $row['payment_price_type'];
	$payment_price_amount = (float) $row['payment_price_amount'];
	$payment_price_name   = $row['payment_price_name'];

	$payment_enable_recurring = (int) $row['payment_enable_recurring'];
	$payment_recurring_cycle  = (int) $row['payment_recurring_cycle'];
	$payment_recurring_unit   = $row['payment_recurring_unit'];

	$payment_enable_trial = (int) $row['payment_enable_trial'];
	$payment_trial_period = (int) $row['payment_trial_period'];
	$payment_trial_unit   = $row['payment_trial_unit'];
	$payment_trial_amount = (float) $row['payment_trial_amount'];

	$payment_enable_discount = (int) $row['payment_enable_discount'];
	$payment_discount_type 	 = $row['payment_discount_type'];
	$payment_discount_amount = (float) $row['payment_discount_amount'];
	$payment_discount_element_id = (int) $row['payment_discount_element_id'];

	$payment_delay_notifications = (int) $row['payment_delay_notifications'];

	$is_discount_applicable = false;

	//if the discount element for the current entry_id having any value, we can be certain that the discount code has been validated and applicable
	if(!empty($payment_enable_discount)){
		$query = "select element_{$payment_discount_element_id} coupon_element from ".MF_TABLE_PREFIX."form_{$form_id} where `id` = ? and `status` = 1";
		$params = array($payment_record_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		if(!empty($row['coupon_element'])){
			$is_discount_applicable = true;
		}
	}

	if(!empty($payment_enable_merchant) && $payment_merchant_type == 'stripe'){
		
		if(!empty($payment_stripe_enable_test_mode)){
			$stripe_secret_key = $payment_stripe_test_secret_key;
		}else{
			$stripe_secret_key = $payment_stripe_live_secret_key;
		}

		//calculate payment amount
		if($payment_price_type == 'fixed'){ 
				
			$charge_amount = $payment_price_amount * 100; //charge in cents
		}else if($payment_price_type == 'variable'){ 
				
			$charge_amount = (double) mf_get_payment_total($dbh,$form_id,$payment_record_id,0,'live');
			$charge_amount = $charge_amount * 100;
		}

		//calculate discount if applicable
		if($is_discount_applicable){
			$payment_calculated_discount = 0;

			if($payment_discount_type == 'percent_off'){
				//the discount is percentage
				$payment_calculated_discount = ($payment_discount_amount / 100) * $charge_amount;
				$payment_calculated_discount = round($payment_calculated_discount,2); //round to 2 digits decimal
			}else{
				//the discount is fixed amount
				$payment_calculated_discount = round($payment_discount_amount,2); //round to 2 digits decimal
			}

			$charge_amount -= $payment_calculated_discount;
		}

		//calculate tax if enabled
		if(!empty($payment_enable_tax) && !empty($payment_tax_rate)){
			$payment_tax_amount = ($payment_tax_rate / 100) * $charge_amount;
			$payment_tax_amount = round($payment_tax_amount); //we need to round it without decimal, since stripe only accept charges in cents, without any decimal 
			$charge_amount += $payment_tax_amount;
		}

		//set private key
		Stripe::setApiKey($stripe_secret_key);

		//create Customer object
		$customer_desc = "Customer for (Form #{$form_id} - Entry #{$payment_record_id})";
		$customer_name = trim($payment_data['first_name'].' '.$payment_data['last_name']);
		if(!empty($customer_name)){
			$customer_desc .= " - {$customer_name}";
		}
		
		
		try {
			$customer_obj = Stripe_Customer::create(array(
								"card" => $token,
						  		"description" => $customer_desc)
							);
		}catch(Stripe_CardError $e) {
		 	//Since it's a decline, Stripe_CardError will be caught
		  	$payment_message = $e->getMessage();
		}catch(Stripe_InvalidRequestError $e) {
		  	//Invalid parameters were supplied to Stripe's API
			$payment_message = $e->getMessage();
		}catch(Stripe_AuthenticationError $e) {
		  	//Authentication with Stripe's API failed
		  	//(maybe you changed API keys recently)
			$payment_message = $e->getMessage();
		}catch (Stripe_ApiConnectionError $e) {
		  	//Network communication with Stripe failed
			$payment_message = $e->getMessage();
		}catch (Stripe_Error $e) {
		  	//Display a very generic error to the user
		  	$payment_message = $e->getMessage();
		}catch (Exception $e) {
		  	//Something else happened, completely unrelated to Stripe
			$payment_message = $e->getMessage();
		}

		if(empty($payment_message)){ //if no error with the card, continue creating charges
			if(!empty($payment_enable_recurring)){ //this is recurring payments
				
				$trial_period_days = 0;
				if(!empty($payment_enable_trial)){
					if($payment_trial_unit == 'day'){
						$trial_period_days = $payment_trial_period;
					}else if($payment_trial_unit == 'week'){
						$trial_period_days = $payment_trial_period * 7;
					}else if($payment_trial_unit == 'month'){
						$trial_period_days = $payment_trial_period * 30;
					}else if($payment_trial_unit == 'year'){
						$trial_period_days = $payment_trial_period * 365;
					}
				}

				$plan_desc = "Plan for (Form #{$form_id} - Entry #{$payment_record_id})";
				if(!empty($customer_name)){
					$plan_desc .= " - {$customer_name}";
				}
				$plan_id = "form{$form_id}_entry{$payment_record_id}";

				//if paid trial enabled, create an invoice item
				if(!empty($payment_enable_trial) && !empty($payment_trial_amount)){
					
					$trial_charge_amount = $payment_trial_amount * 100; //charge in cents
					
					$trial_charge_desc = "Trial Period Payment for (Form #{$form_id} - Entry #{$payment_record_id})";
					if(!empty($customer_name)){
						$trial_charge_desc .= " - {$customer_name}";
					}
					
					Stripe_InvoiceItem::create(array( 
													"customer" => $customer_obj, 
													"amount" => $trial_charge_amount, 
													"currency" => $payment_currency, 
													"description" => $trial_charge_desc) 
												);
				}

				//create subscription plan
				Stripe_Plan::create(array(
										  "amount" => $charge_amount,
										  "interval" => $payment_recurring_unit,
										  "interval_count" => $payment_recurring_cycle,
										  "trial_period_days" => $trial_period_days,
										  "name" => $plan_desc,
										  "currency" => $payment_currency,
										  "id" => $plan_id)
										);

				//subscribe the customer to the plan
				$subscribe_result = $customer_obj->updateSubscription(array("plan" => $plan_id));
				if(!empty($subscribe_result->status)){
					$payment_success = true;

					$payment_data['payment_id'] 	= $subscribe_result->id;
					$payment_data['payment_date'] 	= date("Y-m-d H:i:s",$subscribe_result->start);
					$payment_data['payment_currency'] = $subscribe_result->plan->currency;

					if(!empty($payment_enable_trial) && !empty($payment_trial_amount)){
						$payment_data['payment_amount'] = $trial_charge_amount / 100;
					}else{
						$payment_data['payment_amount'] = $subscribe_result->plan->amount / 100;
					}

					if($subscribe_result->plan->livemode === true){
						$payment_data['payment_test_mode'] = 0;
					}else{
						$payment_data['payment_test_mode'] = 1;
					}
				}
			}else{ //this is non recurring payment
				
				$charge_desc = "Payment for (Form #{$form_id} - Entry #{$payment_record_id})";
				if(!empty($customer_name)){
					$charge_desc .= " - {$customer_name}";
				}

				//charge the customer
				$charge_result = Stripe_Charge::create(array(
					"amount" => $charge_amount,
				  	"currency" => $payment_currency,
				  	"customer" => $customer_obj->id,
				  	"description" => $charge_desc)
				);

				if($charge_result->paid === true){
					$payment_success = true;

					$payment_data['payment_id'] 	= $charge_result->id;
					$payment_data['payment_date'] 	= date("Y-m-d H:i:s");
					$payment_data['payment_amount'] = $charge_result->amount / 100;
					$payment_data['payment_currency'] = $charge_result->currency;

					if($charge_result->livemode === true){
						$payment_data['payment_test_mode'] = 0;
					}else{
						$payment_data['payment_test_mode'] = 1;
					}
				}
			}
		}


	}else{
		$payment_message = "Error. Stripe is not enabled for this form.";
	}


	if($payment_success === true){
		$payment_status = "ok";
		$_SESSION['mf_payment_completed'][$form_id] = true;

		//revoke access to form payment page
		unset($_SESSION['mf_form_payment_access'][$form_id]);

		//insert into ap_form_payments table
		$payment_data['payment_fullname'] = trim($payment_data['first_name'].' '.$payment_data['last_name']);
		$payment_data['form_id'] 		  = $form_id;
		$payment_data['record_id'] 		  = $payment_record_id;
		$payment_data['date_created']	  = date("Y-m-d H:i:s");
		$payment_data['status']			  = 1;
		$payment_data['payment_status']   = 'paid'; 

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
								`status`, 
								`billing_street`, 
								`billing_city`, 
								`billing_state`, 
								`billing_zipcode`, 
								`billing_country`, 
								`same_shipping_address`, 
								`shipping_street`, 
								`shipping_city`, 
								`shipping_state`, 
								`shipping_zipcode`, 
								`shipping_country`) 
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
								:status, 
								:billing_street, 
								:billing_city, 
								:billing_state, 
								:billing_zipcode, 
								:billing_country, 
								:same_shipping_address, 
								:shipping_street, 
								:shipping_city, 
								:shipping_state, 
								:shipping_zipcode, 
								:shipping_country)";		
		
		$params = array();
		$params[':form_id'] 		  	= $payment_data['form_id'];
		$params[':record_id'] 			= $payment_data['record_id'];
		$params[':payment_id'] 			= $payment_data['payment_id'];
		$params[':date_created'] 		= $payment_data['date_created'];
		$params[':payment_date'] 		= $payment_data['payment_date'];
		$params[':payment_status'] 		= $payment_data['payment_status'];
		$params[':payment_fullname']  	= $payment_data['payment_fullname'];
		$params[':payment_amount'] 	  	= $payment_data['payment_amount'];
		$params[':payment_currency']  	= $payment_data['payment_currency'];
		$params[':payment_test_mode'] 	= $payment_data['payment_test_mode'];
		$params[':payment_merchant_type'] = 'stripe';
		$params[':status'] 			  	= $payment_data['status'];
		$params[':billing_street'] 		= $payment_data['billing_street'];
		$params[':billing_city']		= $payment_data['billing_city'];
		$params[':billing_state'] 		= $payment_data['billing_state'];
		$params[':billing_zipcode'] 	= $payment_data['billing_zipcode'];
		$params[':billing_country'] 	= $payment_data['billing_country'];
		$params[':same_shipping_address'] = $payment_data['same_shipping_address'];
		$params[':shipping_street'] 	= $payment_data['shipping_street'];
		$params[':shipping_city'] 		= $payment_data['shipping_city'];
		$params[':shipping_state'] 		= $payment_data['shipping_state'];
		$params[':shipping_zipcode'] 	= $payment_data['shipping_zipcode'];
		$params[':shipping_country'] 	= $payment_data['shipping_country'];

		mf_do_query($query,$params,$dbh);
	}else{
		$payment_status = "error";
	}
	
	$response_data = new stdClass();
	$response_data->status    	= $payment_status;
	$response_data->form_id 	= $form_id;
	$response_data->message 	= $payment_message;
	
	$response_json = json_encode($response_data);
	echo $response_json;
?>
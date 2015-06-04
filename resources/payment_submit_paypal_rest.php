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
	require('lib/HttpClient.class.php');
	
	$form_id 			= (int) trim($_POST['form_id']);
	$payment_record_id 	= $_SESSION['mf_payment_record_id'][$form_id];
	$payment_data 		= mf_sanitize($_POST['payment_properties']);

	$customer_ip	    = $_SERVER['REMOTE_ADDR'];

	$payment_success  = false;
	$payment_message = '';
	
	if(empty($form_id) || empty($payment_record_id)){
		$response_data = new stdClass();
		$response_data->status    	= "error";
		$response_data->message 	= "Error. Your session has been expired. Please start the form again.";
		
		$response_json = json_encode($response_data);
		echo $response_json;
		
		exit;
	}

	$dbh = mf_connect_db();

	//use cURL extension to make api call if available
	if(function_exists('curl_init')){
		$use_curl = true;
	}else{
		$use_curl = false;
	}
	
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
					payment_paypal_rest_live_clientid,
					payment_paypal_rest_live_secret_key,
					payment_paypal_rest_test_clientid,
					payment_paypal_rest_test_secret_key,
					payment_paypal_rest_enable_test_mode,
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

	$payment_currency 	   		 = strtoupper($row['payment_currency']);
	$payment_price_type 	     = $row['payment_price_type'];
	$payment_price_amount    	 = $row['payment_price_amount'];
	$payment_ask_billing 	 	 = (int) $row['payment_ask_billing'];
	$payment_ask_shipping 	 	 = (int) $row['payment_ask_shipping'];
	$payment_merchant_type		 = $row['payment_merchant_type'];
	

	$payment_paypal_rest_live_clientid  	= trim($row['payment_paypal_rest_live_clientid']);
	$payment_paypal_rest_live_secret_key  	= trim($row['payment_paypal_rest_live_secret_key']);
	$payment_paypal_rest_test_clientid  	= trim($row['payment_paypal_rest_test_clientid']);
	$payment_paypal_rest_test_secret_key  	= trim($row['payment_paypal_rest_test_secret_key']);
	$payment_paypal_rest_enable_test_mode  	= (int) $row['payment_paypal_rest_enable_test_mode'];
	
	$payment_price_type   = $row['payment_price_type'];
	$payment_price_amount = (float) $row['payment_price_amount'];
	$payment_price_name   = $row['payment_price_name'];

	$payment_enable_recurring = 0; //PayPal REST API currently doesn't support recurring payments
	
	$payment_enable_discount = (int) $row['payment_enable_discount'];
	$payment_discount_type 	 = $row['payment_discount_type'];
	$payment_discount_amount = (float) $row['payment_discount_amount'];
	$payment_discount_element_id = (int) $row['payment_discount_element_id'];

	$payment_delay_notifications = (int) $row['payment_delay_notifications'];

	if(!empty($payment_data['same_shipping_address'])){
		$payment_data['shipping_street']	= $payment_data['billing_street'];
		$payment_data['shipping_city']		= $payment_data['billing_city'];
		$payment_data['shipping_state']		= $payment_data['billing_state'];
		$payment_data['shipping_zipcode'] 	= $payment_data['billing_zipcode'];
		$payment_data['shipping_country'] 	= $payment_data['billing_country'];
	}
	
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

	if(!empty($payment_enable_merchant) && $payment_merchant_type == 'paypal_rest'){
		
		if(!empty($payment_paypal_rest_enable_test_mode)){
			$api_client_id	= $payment_paypal_rest_test_clientid;
			$api_secret 	= $payment_paypal_rest_test_secret_key;
			$api_end_point	= 'https://api.sandbox.paypal.com';
		}else{
			$api_client_id	= $payment_paypal_rest_live_clientid;
			$api_secret 	= $payment_paypal_rest_live_secret_key;
			$api_end_point	= 'https://api.paypal.com';
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

		$customer_name = trim($payment_data['first_name'].' '.$payment_data['last_name']);

		if(!empty($payment_enable_recurring)){ //this is recurring payments
			
			//Place the recurring code here once PayPal REST API support it

		}else{ //this is non recurring payment
			
			$charge_desc = "Payment for (Form #{$form_id} - Entry #{$payment_record_id})";
			if(!empty($customer_name)){
				$charge_desc .= " - {$customer_name}";
			}

			//charge the customer
			$charge_amount_dollar =	sprintf("%.2f",round($charge_amount / 100, 2)); //paypal need exactly 2 digit decimal

			//authorize and get access token
			
			if($use_curl){
				$ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL,$api_end_point.'/'.'v1/oauth2/token');
			    curl_setopt($ch, CURLOPT_USERPWD, $api_client_id.":".$api_secret);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			    curl_setopt($ch, CURLOPT_POST, 1);
			    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
			    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
			    curl_setopt($ch, CURLOPT_HEADER , 0);   
			    curl_setopt($ch, CURLOPT_VERBOSE, 1);
			    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			
			    $api_response_raw = curl_exec($ch);
			    
			    curl_close($ch);
			}else{
				$post_headers = array('Accept' => 'application/json','Connection' => 'close');
				$post_data	  = array('grant_type' => "client_credentials");

	  			$paypal_rest_client = new HttpClient($api_end_point);
	  			$paypal_rest_client->setHttpVersion('HTTP/1.1');
	  			$paypal_rest_client->setAuthorization($api_client_id, $api_secret);
	  			$paypal_rest_client->post('v1/oauth2/token', $post_data, $post_headers);

				$api_response_raw  = $paypal_rest_client->getContent();
			}
			
			$api_response_json = json_decode($api_response_raw);  
			$access_token = $api_response_json->access_token;
			
			if(empty($access_token)){
				$payment_message = 'Error obtaining access token to PayPal. '.$api_response_json->error_description;
			}else{
				
				$credit_card_type = mf_get_credit_card_type($payment_data['card_number']);
				if(!empty($payment_data['card_cvc'])){
					$credit_card_cvv_json = '"cvv2": "'.$payment_data['card_cvc'].'",';
				}

				$billing_country_code = mf_get_country_code($payment_data['billing_country']);
				$shipping_country_code = mf_get_country_code($payment_data['shipping_country']);

				if(!empty($payment_data['billing_street'])){
					$billing_address_json =<<<EOT
						,"billing_address": {
	                        "line1": "{$payment_data['billing_street']}",
	                        "city": "{$payment_data['billing_city']}",
	                        "country_code": "{$billing_country_code}",
	                        "postal_code": "{$payment_data['billing_zipcode']}",
	                        "state": "{$payment_data['billing_state']}"
                    	}
EOT;
				}

				if(!empty($payment_data['shipping_street'])){
					$shipping_address_json =<<<EOT
						,"item_list": {
			                "shipping_address": {
			                    "recipient_name": "{$customer_name}",
			                    "line1": "{$payment_data['shipping_street']}",
			                    "city": "{$payment_data['shipping_city']}",
			                    "country_code": "{$shipping_country_code}",
			                    "postal_code": "{$payment_data['shipping_zipcode']}",
			                    "state": "{$payment_data['shipping_state']}"
			                }
			            }
EOT;
				}

				
				$post_data =<<<EOT
{
    "intent": "sale",
    "payer": {
        "payment_method": "credit_card",
        "funding_instruments": [
            {
                "credit_card": {
                    "number": "{$payment_data['card_number']}",
                    "type": "{$credit_card_type}",
                    "expire_month": "{$payment_data['card_exp_month']}",
                    "expire_year": "{$payment_data['card_exp_year']}",
                    {$credit_card_cvv_json}
                    "first_name": "{$payment_data['first_name']}",
                    "last_name": "{$payment_data['last_name']}"
                    {$billing_address_json}
                }
            }
        ]
    },
    "transactions": [
        {
            "amount": {
                "total": "{$charge_amount_dollar}",
                "currency": "{$payment_currency}"
            },
            "description": "{$charge_desc}"
            {$shipping_address_json}
        }
    ]
}
EOT;
				
				if($use_curl){
					$ch = curl_init();
				    curl_setopt($ch, CURLOPT_URL,$api_end_point.'/'.'v1/payments/payment');
				    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				    curl_setopt($ch, CURLOPT_POST, 1);
				    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
				    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
																'Authorization: Bearer '.$access_token,
																'Accept: application/json',
																'Content-Type: application/json'
																));
				    curl_setopt($ch, CURLOPT_HEADER , 0);   
				    curl_setopt($ch, CURLOPT_VERBOSE, 1);
				    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				
				    $api_response_raw = curl_exec($ch);
				
				    curl_close($ch);
				}else{
					$post_headers = array('Authorization' => 'Bearer '.$access_token,'Content-Type' => 'application/json','Connection' => 'close');

					$paypal_rest_client = new HttpClient($api_end_point);
					$paypal_rest_client->setHttpVersion('HTTP/1.1');
		  			$paypal_rest_client->post('v1/payments/payment', $post_data, $post_headers);

					$api_response_raw  = $paypal_rest_client->getContent();
				}

				$api_response_json = json_decode($api_response_raw);

				if(!empty($api_response_json->id)){
					$payment_success = true;

					$payment_data['payment_id'] 		= $api_response_json->id;
					$payment_data['payment_date'] 		= date("Y-m-d H:i:s");
					$payment_data['payment_amount'] 	= $charge_amount_dollar;
					$payment_data['payment_currency'] 	= $payment_currency;
					$payment_data['payment_test_mode'] 	= $payment_paypal_rest_enable_test_mode;
				}else{

					$error_name = $api_response_json->name;
					
					switch ($error_name) {
						case 'INTERNAL_SERVICE_ERROR':
							$payment_message = 'An internal service error on PayPal server has occured.';
							break;
						case 'VALIDATION_ERROR':
							$payment_message = 'Validation Error. '.$api_response_json->details[0]->issue;
							break;
						case 'EXPIRED_CREDIT_CARD':
							$payment_message = 'Credit card is expired.';
							break;
						case 'TRANSACTION_LIMIT_EXCEEDED':
							$payment_message = 'Total payment amount exceeded transaction limit.';
							break;
						case 'TRANSACTION_REFUSED':
							$payment_message = 'This request was refused.';
							break;
						case 'CREDIT_CARD_REFUSED':
							$payment_message = 'Credit card was refused.';
							break;
						case 'CREDIT_CARD_CVV_CHECK_FAILED':
							$payment_message = 'The credit card CVV check failed.';
							break;
						default:
							$payment_message = $api_response_json->name.' - '.$api_response_json->message.' - '.$api_response_json->details[0]->issue;
							break;
					}
					
				}				

			}

		}

	}else{
		$payment_message = "Error. PayPal Pro - REST API is not enabled for this form.";
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
		$payment_data['date_created']	  = $payment_data['payment_date'];
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
		$params[':payment_merchant_type'] = 'paypal_rest';
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





	/** Functions **/
	//simple function to get credit card type by number
	function mf_get_credit_card_type($card_number){

      $regex  = '/^4[0-9]{12}(?:[0-9]{3})?$/';
      $result = preg_match($regex, $card_number);

      if(!empty($result)){
        return 'visa';
      }

      $regex  = '/^5[1-5][0-9]{14}$/';
      $result = preg_match($regex, $card_number);

      if(!empty($result)){
        return 'mastercard';
      }

      $regex  = '/^6(?:011|5[0-9]{2})[0-9]{12}$/';
      $result = preg_match($regex, $card_number);

      if(!empty($result)){
        return 'discover';
      }

      $regex  = '/^3[47][0-9]{13}$/';
      $result = preg_match($regex, $card_number);

      if(!empty($result)){
        return 'amex';
      }

      $regex  = '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/';
      $result = preg_match($regex, $card_number);

      if(!empty($result)){
        return 'diners';
      }

      $regex  = '/^(?:2131|1800|35\d{3})\d{11}$/';
      $result = preg_match($regex, $card_number);

      if(!empty($result)){
        return 'jcb';
      }
  }

?>
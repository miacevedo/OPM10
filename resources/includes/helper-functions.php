<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2014 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	
	//this function accept 'YYYY-MM-DD HH:MM:SS'
	function mf_relative_date($input_date) {
	    
	    $tz = 0;    // change this if your web server and weblog are in different timezones
	           			        
	    $posted_date = str_replace(array('-',' ',':'),'',$input_date);            
	    $month = substr($posted_date,4,2);
	    
	    if ($month == "02") { // february
	    	// check for leap year
	    	$leapYear = mf_is_leap_year(substr($posted_date,0,4));
	    	if ($leapYear) $month_in_seconds = 2505600; // leap year
	    	else $month_in_seconds = 2419200;
	    }
	    else { // not february
	    // check to see if the month has 30/31 days in it
	    	if ($month == "04" or 
	    		$month == "06" or 
	    		$month == "09" or 
	    		$month == "11")
	    		$month_in_seconds = 2592000; // 30 day month
	    	else $month_in_seconds = 2678400; // 31 day month;
	    }
	  
	    $in_seconds = strtotime(substr($posted_date,0,8).' '.
	                  substr($posted_date,8,2).':'.
	                  substr($posted_date,10,2).':'.
	                  substr($posted_date,12,2));
	    $diff = time() - ($in_seconds + ($tz*3600));
	    $months = floor($diff/$month_in_seconds);
	    $diff -= $months*2419200;
	    $weeks = floor($diff/604800);
	    $diff -= $weeks*604800;
	    $days = floor($diff/86400);
	    $diff -= $days*86400;
	    $hours = floor($diff/3600);
	    $diff -= $hours*3600;
	    $minutes = floor($diff/60);
	    $diff -= $minutes*60;
	    $seconds = $diff;
	
	    $relative_date = '';
	    if ($months>0) {
	        // over a month old, just show date ("Month, Day Year")
	        if(!empty($input_date)){
	        	return date('F jS, Y',strtotime($input_date));
	        }else{
	        	return 'N/A';
	        }
	    } else {
	        if ($weeks>0) {
	            // weeks and days
	            $relative_date .= ($relative_date?', ':'').$weeks.' week'.($weeks>1?'s':'');
	            $relative_date .= $days>0?($relative_date?', ':'').$days.' day'.($days>1?'s':''):'';
	        } elseif ($days>0) {
	            // days and hours
	            $relative_date .= ($relative_date?', ':'').$days.' day'.($days>1?'s':'');
	            $relative_date .= $hours>0?($relative_date?', ':'').$hours.' hour'.($hours>1?'s':''):'';
	        } elseif ($hours>0) {
	            // hours and minutes
	            $relative_date .= ($relative_date?', ':'').$hours.' hour'.($hours>1?'s':'');
	            $relative_date .= $minutes>0?($relative_date?', ':'').$minutes.' minute'.($minutes>1?'s':''):'';
	        } elseif ($minutes>0) {
	            // minutes only
	            $relative_date .= ($relative_date?', ':'').$minutes.' minute'.($minutes>1?'s':'');
	        } else {
	            // seconds only
	            $relative_date .= ($relative_date?', ':'').$seconds.' second'.($seconds>1?'s':'');
	        }
	        
	        // show relative date and add proper verbiage
	    	return $relative_date.' ago';
	    }
	    
	}
	
	//this function accept 'YYYY-MM-DD HH:MM:SS'
	function mf_short_relative_date($input_date) {
	    
	    $tz = 0;    // change this if your web server and weblog are in different timezones
	           			        
	    $posted_date = str_replace(array('-',' ',':'),'',$input_date);            
	    $month = substr($posted_date,4,2);
	    $year  = substr($posted_date,0,4);
	    
	    if ($month == "02") { // february
	    	// check for leap year
	    	$leapYear = mf_is_leap_year($year);
	    	if ($leapYear) $month_in_seconds = 2505600; // leap year
	    	else $month_in_seconds = 2419200;
	    }
	    else { // not february
	    // check to see if the month has 30/31 days in it
	    	if ($month == "04" or 
	    		$month == "06" or 
	    		$month == "09" or 
	    		$month == "11")
	    		$month_in_seconds = 2592000; // 30 day month
	    	else $month_in_seconds = 2678400; // 31 day month;
	    }
	  
	    $in_seconds = strtotime(substr($posted_date,0,8).' '.
	                  substr($posted_date,8,2).':'.
	                  substr($posted_date,10,2).':'.
	                  substr($posted_date,12,2));
	    $diff = time() - ($in_seconds + ($tz*3600));
	    $months = floor($diff/$month_in_seconds);
	    $diff -= $months*2419200;
	    $weeks = floor($diff/604800);
	    $diff -= $weeks*604800;
	    $days = floor($diff/86400);
	    $diff -= $days*86400;
	    $hours = floor($diff/3600);
	    $diff -= $hours*3600;
	    $minutes = floor($diff/60);
	    $diff -= $minutes*60;
	    $seconds = $diff;
	
	    $relative_date = '';
	    if ($months>0) {
	    	
	        // over a month old
	        if(!empty($input_date)){
	        	if($year < date('Y')){ //over a year, show international date
	        		return date('Y-m-d',strtotime($input_date));
	        	}else{ //less than a year
	        		return date('M j',strtotime($input_date));
	        	}
	        	
	        }else{
	        	return '';
	        }
	    } else {
	        if ($weeks>0) {
	            // weeks and days
	            $relative_date .= ($relative_date?', ':'').$weeks.' week'.($weeks>1?'s':'');
	            //$relative_date .= $days>0?($relative_date?', ':'').$days.' day'.($days>1?'s':''):'';
	        } elseif ($days>0) {
	            // days and hours
	            $relative_date .= ($relative_date?', ':'').$days.' day'.($days>1?'s':'');
	            //$relative_date .= $hours>0?($relative_date?', ':'').$hours.' hour'.($hours>1?'s':''):'';
	        } elseif ($hours>0) {
	            // hours and minutes
	            $relative_date .= ($relative_date?', ':'').$hours.' hour'.($hours>1?'s':'');
	            //$relative_date .= $minutes>0?($relative_date?', ':'').$minutes.' minute'.($minutes>1?'s':''):'';
	        } elseif ($minutes>0) {
	            // minutes only
	            $relative_date .= ($relative_date?', ':'').$minutes.' minute'.($minutes>1?'s':'');
	        } else {
	            // seconds only
	            $relative_date .= ($relative_date?', ':'').$seconds.' second'.($seconds>1?'s':'');
	        }
	        
	        // show relative date and add proper verbiage
	    	return $relative_date.' ago';
	    }
	    
	}
	
	function mf_is_leap_year($year) {
	        return $year % 4 == 0 && ($year % 400 == 0 || $year % 100 != 0);
	}
	
	//remove a folder and all it's content
	function mf_full_rmdir($dirname){
        if ($dirHandle = opendir($dirname)){
            $old_cwd = getcwd();
            chdir($dirname);

            while ($file = readdir($dirHandle)){
                if ($file == '.' || $file == '..') continue;

                if (is_dir($file)){
                    if (!mf_full_rmdir($file)) return false;
                }else{
                    if (!unlink($file)) return false;
                }
            }

            closedir($dirHandle);
            chdir($old_cwd);
            if (!rmdir($dirname)) return false;

            return true;
        }else{
            return false;
        }
    }

    //show success or error messages
    function mf_show_message(){
    	
    	if(!empty($_SESSION['MF_SUCCESS'])){
    		
    		$message_div = <<<EOT
    		    <div class="gradient_blue content_notification">
					<div class="cn_icon">
						<span class="icon-checkmark-circle"></span>
					</div>
					<div class="cn_message">
						<h6 style="font-size: 16px">Success!</h6>
						<h6>{$_SESSION['MF_SUCCESS']}</h6>
					</div>
					<a id="close_notification" href="#" onclick="$('.content_notification').fadeOut();return false;" title="Close Notification"><img src="images/icons/52_blue_16.png" /></a>
				</div>
EOT;
    		
    		$_SESSION['MF_SUCCESS'] = '';
    		
    		echo $message_div;
    	}else if(!empty($_SESSION['MF_ERROR'])){
    		$message_div = <<<EOT
    		    <div class="gradient_red content_notification">
					<div class="cn_icon">
						<span class="icon-notification"></span>
					</div>
					<div class="cn_message">
						<h6 style="font-size: 16px">Error!</h6>
						<h6>{$_SESSION['MF_ERROR']}</h6>
					</div>
					<a id="close_notification" href="#" onclick="$('.content_notification').fadeOut();return false;" title="Close Notification"><img src="images/icons/52_red_16.png" /></a>
				</div>
EOT;
    		
    		$_SESSION['MF_ERROR'] = '';
    		
    		echo $message_div;
    	}

    }

    //send form data to an URL
    function mf_send_webhook_notification($dbh,$form_id,$entry_id,$webhook_rule_id){
    	
    	global $mf_lang;

    	$form_id = (int) $form_id;

    	//get webhook settings
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
			    	 form_id = ? and rule_id = ?";
		$params = array($form_id,$webhook_rule_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$webhook_url						= $row['webhook_url'];
		$webhook_method 					= strtolower($row['webhook_method']);
		$webhook_format 					= $row['webhook_format'];
		$webhook_raw_data 					= $row['webhook_raw_data'];
		$webhook_enable_http_auth 			= (int) $row['enable_http_auth'];
		$webhook_http_username 				= $row['http_username'];
		$webhook_http_password 				= $row['http_password'];
		$webhook_enable_custom_http_headers = (int) $row['enable_custom_http_headers'];
		$webhook_custom_http_headers 		= $row['custom_http_headers'];

    	//get parameters from the table
    	$webhook_parameters = array();
		$query = "select param_name,param_value from ".MF_TABLE_PREFIX."webhook_parameters where form_id = ? and rule_id = ? order by awp_id asc";
		$params = array($form_id,$webhook_rule_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$i=0;
		while($row = mf_do_fetch_result($sth)){
			$webhook_parameters[$i]['param_name']  = $row['param_name'];
			$webhook_parameters[$i]['param_value'] = $row['param_value'];
			$i++;
		}

		//get template variables -------------
		$mf_settings = mf_get_settings($dbh);

    	$template_data_options['strip_download_link']  = false; 
    	$template_data_options['as_plain_text']		   = true;
    	$template_data_options['target_is_admin'] 	   = false;
		$template_data_options['machform_path'] 	   = $mf_settings['base_url'];
		
		$template_data = mf_get_template_variables($dbh,$form_id,$entry_id,$template_data_options);
		
		$template_variables = $template_data['variables'];
		$template_values    = $template_data['values'];

    	//replace any template variables within webhook parameters
    	$webhook_data = array();

    	if($webhook_format == 'key-value'){
	    	foreach ($webhook_parameters as $value) {
	    		$param_name  = $value['param_name'];
	    		$param_value = str_replace($template_variables, $template_values, $value['param_value']);

	    		$webhook_data[$param_name] = $param_value;
	    	}
    	}else if($webhook_format == 'raw'){
    		$webhook_data = str_replace($template_variables, $template_values, $webhook_raw_data);
    	}

    	//send the data to the URL
		$webhook_url_info = parse_url($webhook_url);

		if(!empty($webhook_url_info['port'])){
			$webhook_client = new HttpClient($webhook_url_info['host'], $webhook_url_info['port']);
		}else{
			$webhook_client = new HttpClient($webhook_url_info['host']);
			$webhook_client->setScheme($webhook_url_info['scheme']);
		}

		//set this to 'true' to enable debug mode
		$webhook_client->setDebug(false);

		//if the webhook URL contain the http auth username and password, use it
		if(!empty($webhook_url_info['user']) && !empty($webhook_url_info['pass'])){
			$webhook_client->setAuthorization($webhook_url_info['user'], $webhook_url_info['pass']);
		}

		//if the user enabled http auth and provided the username and password
		if(!empty($webhook_enable_http_auth)){
			$webhook_client->setAuthorization($webhook_http_username, $webhook_http_password);
		}

		//prepare headers
		$webhook_headers = array();
		if(!empty($webhook_enable_custom_http_headers)){
			$headers_array = get_object_vars(json_decode($webhook_custom_http_headers));
			
			if(!empty($headers_array)){
				foreach ($headers_array as $key => $value) {
					$webhook_headers[$key] = $value;
				}
			}
		}
		
		if($webhook_method == 'post'){
			$webhook_client->post($webhook_url_info['path'], $webhook_data, $webhook_headers);
		}elseif ($webhook_method == 'get') {
			$webhook_client->get($webhook_url_info['path'], $webhook_data, $webhook_headers);
		}elseif ($webhook_method == 'put') {
			echo "running PUT";
			$webhook_client->put($webhook_url_info['path'], $webhook_data, $webhook_headers);
		}

		$webhook_status = $webhook_client->getStatus();
		$http_success_codes = array('200','201','202','203','204','205','206','207','208','226');

		if(!in_array($webhook_status,$http_success_codes)){
			echo "Error Sending Webhooks! ";
			switch ($webhook_status) {
				case '404':
					echo "Website URL Not Found ({$webhook_url})";
					break;
				case '401':
					echo "Unauthorized Access. Incorrect HTTP Username/Password for Website URL.";
					break;
				case '403':
					echo "Forbidden. You don't have permission to access the Website URL.";
					break;
				case '302':
					echo "Page Moved Temporarily.";
					break;
				case '307':
					echo "Page Moved Permanently.";
					break;
				case '500':
					echo "Internal Server Error.";
					break;
				default:
					echo "Error Code: ({$webhook_status})";
					break;
			}
			var_dump($webhook_client->getContent());
		}

    }
    
    //send notification email
    //$to_emails is a comma separated list of email address or {element_x} field
    function mf_send_notification($dbh,$form_id,$entry_id,$to_emails,$email_param){
    	
    	global $mf_hook_emails;
    	global $mf_lang;

    	$form_id = (int) $form_id;

    	$template_data_options = array();

    	$from_name  	= $email_param['from_name'];
    	$from_email 	= $email_param['from_email'];
    	$replyto_email  = $email_param['replyto_email'];

    	$subject 	= $email_param['subject'];
    	$content 	= $email_param['content'];
    	$as_plain_text 		= $email_param['as_plain_text']; //if set to 'true' the email content will be a simple plain text
    	$target_is_admin 	= $email_param['target_is_admin']; //if set to 'false', the download link for uploaded file will be removed
    	$check_hook_file    = $email_param['check_hook_file'];

		//get settings first
    	$mf_settings = mf_get_settings($dbh);

    	//get template variables data
    	if($target_is_admin === false){
    		$template_data_options['strip_download_link'] = false; //as of v3, receipt email should display download link
    	}

    	$template_data_options['as_plain_text']		   = $as_plain_text;
    	$template_data_options['target_is_admin'] 	   = $target_is_admin;
		$template_data_options['machform_path'] 	   = $email_param['machform_base_path'];
		
		$template_data = mf_get_template_variables($dbh,$form_id,$entry_id,$template_data_options);
		
		$template_variables = $template_data['variables'];
		$template_values    = $template_data['values'];

		//get files to attach, if any
		$entry_options = array();
		$entry_options['strip_download_link'] 	= true;
	    $entry_options['strip_checkbox_image'] 	= true;
	    $entry_options['machform_path'] 		= $email_param['machform_base_path']; //the path to machform
		
		$entry_details = mf_get_entry_details($dbh,$form_id,$entry_id,$entry_options);

		$files_to_attach = array();
		$j=0;
		foreach ($entry_details as $data){
			if ($data['element_type'] == 'file' && !empty($data['filedata'])){
				//if there is file to be attached
				foreach ($data['filedata'] as $file_info){
							$files_to_attach[$j]['filename_path']  = $file_info['filename_path'];
							$files_to_attach[$j]['filename_value'] = $file_info['filename_value'];
							$j++;
				}
			}
		}	


		//create the mail transport
		if(!empty($mf_settings['smtp_enable'])){
			$s_transport = Swift_SmtpTransport::newInstance($mf_settings['smtp_host'], $mf_settings['smtp_port']);
			
			if(!empty($mf_settings['smtp_secure'])){
				//port 465 for (SSL), while port 587 for (TLS)
				if($mf_settings['smtp_port'] == '587'){
					$s_transport->setEncryption('tls');
				}else{
					$s_transport->setEncryption('ssl');
				}
			}
			
			if(!empty($mf_settings['smtp_auth'])){
				$s_transport->setUsername($mf_settings['smtp_username']);
  				$s_transport->setPassword($mf_settings['smtp_password']);
			}
		}else{
			$s_transport = Swift_MailTransport::newInstance(); //use PHP mail() transport
		}
		
		//create mailer instance
		$s_mailer = Swift_Mailer::newInstance($s_transport);
		if(file_exists($mf_settings['upload_dir']."/form_{$form_id}/files") && is_writable($mf_settings['upload_dir']."/form_{$form_id}/files")){
			Swift_Preferences::getInstance()->setCacheType('disk')->setTempDir($mf_settings['upload_dir']."/form_{$form_id}/files");
		}else{
			Swift_Preferences::getInstance()->setCacheType('array');
		}
		
		//create the message
    	//parse from_name template
    	if(!empty($from_name)){
    		$from_name = str_replace($template_variables,$template_values,$from_name);
			$from_name = str_replace('&nbsp;','',$from_name);
			
			//decode any html entity
			$from_name = html_entity_decode($from_name,ENT_QUOTES);

			if(empty($from_name)){
				if(!empty($mf_settings['default_from_name'])){
	    			$from_name = $mf_settings['default_from_name'];
	    		}else{
	    			$from_name = 'MachForm';	
	    		}
			}
    	}else{
    		if(!empty($mf_settings['default_from_name'])){
    			$from_name = $mf_settings['default_from_name'];
    		}else{
    			$from_name = 'MachForm';	
    		}
		}
		
    	//parse from_email_address template
    	if(!empty($from_email)){
    		
    		$from_email = str_replace($template_variables,$template_values,$from_email);

    		if(empty($from_email)){
    			
    			if(!empty($mf_settings['default_from_email'])){
    				$from_email = $mf_settings['default_from_email'];
    			}else{
	    			$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
					$from_email = "no-reply@{$domain}";
				}
    		}
		}else{
			if(!empty($mf_settings['default_from_email'])){
    			$from_email = $mf_settings['default_from_email'];
    		}else{
	    		$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
				$from_email = "no-reply@{$domain}";
			}
		}

		//if Reply-To is not being set, use From Email as default
		if(empty($replyto_email)){
			$replyto_email = $from_email;
		}

		//parse Reply-To address template
    	if(!empty($replyto_email)){
    		
    		$replyto_email = str_replace($template_variables,$template_values,$replyto_email);

    		if(empty($replyto_email)){
    			
    			if(!empty($mf_settings['default_from_email'])){
    				$replyto_email = $mf_settings['default_from_email'];
    			}else{
	    			$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
					$replyto_email = "no-reply@{$domain}";
				}
    		}
		}else{
			if(!empty($mf_settings['default_from_email'])){
    			$replyto_email = $mf_settings['default_from_email'];
    		}else{
	    		$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
				$replyto_email = "no-reply@{$domain}";
			}
		}
		
		//parse subject template
    	if(!empty($subject)){
    		$subject = str_replace($template_variables,$template_values,$subject);
			$subject = str_replace('&nbsp;','',$subject);
		}else{
			if($target_is_admin){
				$subject = utf8_encode("{$form_name} [#{$entry_id}]");
			}else{
				$subject = utf8_encode("{$form_name} - Receipt");
			}
		}
		//decode any html entity
		$subject = html_entity_decode($subject,ENT_QUOTES);
		
		//parse content template
    	$email_content = str_replace($template_variables,$template_values,$content);
    	
    	
    	if(!$as_plain_text){ //html type
    		
	    	//enclose with container div
	    	$email_content = '<div style="font-family:Lucida Grande,Tahoma,Arial,Verdana,sans-serif;font-size:12px">'.$email_content.'</div>';
	    }
    	
    	$to_emails 		= str_replace('&nbsp;','',str_replace($template_variables,$template_values,$to_emails));
    	
    	if(!empty($to_emails)){
    		$email_address 	= explode(',',$to_emails);
    	}

    	if(!empty($email_address)){
    		
    		if(!$as_plain_text){
	    		$email_content_type = 'text/html';
	    	}else{
	    		$email_content_type = 'text/plain';	
	    	}

	    	//check for hook file (currently being used to set the destination email based on dropdown/radio button/checkboxes selection)
	    	if($check_hook_file === true){
	    		$hook_emails = $mf_hook_emails[$form_id];
	    		if(!empty($hook_emails)){
	    			$hook_element_id = $hook_emails['element_id'];

	    			//get the field type of this element_id
	    			$query = "select element_type from ".MF_TABLE_PREFIX."form_elements where form_id=? and element_id=? and element_status=1";
	    			$params = array($form_id,$hook_element_id);
	    			$sth = mf_do_query($query,$params,$dbh);	
					$row = mf_do_fetch_result($sth);

					if($row['element_type'] == 'checkbox'){
						//get all selected checkboxes
						$query = "select 
										option_id,
										`option` option_title 
									from 
										".MF_TABLE_PREFIX."element_options 
								   where 
								   		form_id=? and element_id=? and live=1 
								order by 
										option_id asc";
						$params = array($form_id,$hook_element_id);
						$sth = mf_do_query($query,$params,$dbh);

						$checkbox_element_names_array = array();	
						while($row = mf_do_fetch_result($sth)){
							$checkbox_hook_lookup[$row['option_id']] = $row['option_title'];
							$checkbox_element_names_array[] = 'element_'.$hook_element_id.'_'.$row['option_id']; 
						}

						$checkbox_element_names_joined = implode(',', $checkbox_element_names_array);

						$query = "select {$checkbox_element_names_joined} from ".MF_TABLE_PREFIX."form_{$form_id} where `id`=?";
						$params = array($entry_id);
						$sth = mf_do_query($query,$params,$dbh);
						$row = mf_do_fetch_result($sth);

						$selected_checkbox_array = array();
						foreach ($checkbox_hook_lookup as $option_id => $option_title) {
							if(!empty($row['element_'.$hook_element_id.'_'.$option_id])){
								$selected_checkbox_array[] = $option_title;
							}
						}

						if(!empty($selected_checkbox_array)){
							$email_address = array();
							foreach ($selected_checkbox_array as $selected_option_title) {
								$selected_hook_email = $mf_hook_emails[$form_id][$selected_option_title];

								if(!empty($selected_hook_email)){
									$temp_email_address = explode(",",$selected_hook_email);
								}

								$email_address = array_merge($email_address, (array) $temp_email_address);
							}
						}

					}else{
		    			$query = "select 
										B.`option` selected_value 
									from 
										".MF_TABLE_PREFIX."form_{$form_id} A left join ".MF_TABLE_PREFIX."element_options B 
									  on 
									    B.form_id=? and A.element_{$hook_element_id}=B.option_id and B.live=1 and B.element_id=?
									where 
										A.`id`=?";
		    			$params = array($form_id,$hook_element_id,$entry_id);
						$sth = mf_do_query($query,$params,$dbh);	
						$row = mf_do_fetch_result($sth);
						$selected_value = $row['selected_value'];

						$selected_hook_email = $mf_hook_emails[$form_id][$selected_value];
						
						if(!empty($selected_hook_email)){
							$email_address = explode(",",$selected_hook_email);
						}
					}
	    		}
	    	}
	    	
	    	array_walk($email_address, 'mf_trim_value');
	    	
			$s_message = Swift_Message::newInstance()
			->setCharset('utf-8')
			->setMaxLineLength(1000)
			->setSubject($subject)
			->setFrom(array($from_email => $from_name))
			->setReplyTo(array($replyto_email => $from_name))
			->setSender($from_email)
			->setReturnPath($from_email)
			->setTo($email_address)
			->setBody($email_content, $email_content_type);

	    	//attach files, if any
	    	if(!empty($files_to_attach)){
	    		foreach ($files_to_attach as $file_data){
	    			$s_message->attach(Swift_Attachment::fromPath($file_data['filename_path'])->setFilename($file_data['filename_value']));
	    		}
	    	}
			
			//send the message
			$send_result = $s_mailer->send($s_message);
			if(empty($send_result)){
				echo "Error sending email!";
			}
    	}
		
    }
    
    //send all notifications, to admin and users, based on the email settings
    function mf_process_delayed_notifications($dbh,$form_id,$entry_id,$options=array()){
    	
    	$form_id = (int) $form_id;

    	//get form properties data
		$query 	= "select 
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
						 logic_email_enable,
						 webhook_enable
				     from 
				     	 `".MF_TABLE_PREFIX."forms` 
				    where 
				    	 form_id=?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$form_email 	= $row['form_email'];
		
		$esl_from_name 	= $row['esl_from_name'];
		$esl_from_email_address    = $row['esl_from_email_address'];
		$esl_replyto_email_address = $row['esl_replyto_email_address'];
		$esl_subject 	= $row['esl_subject'];
		$esl_content 	= $row['esl_content'];
		$esl_plain_text	= $row['esl_plain_text'];
		$esl_enable     = $row['esl_enable'];
		
		$esr_email_address 	= $row['esr_email_address'];
		$esr_from_name 	= $row['esr_from_name'];
		$esr_from_email_address    = $row['esr_from_email_address'];
		$esr_replyto_email_address = $row['esr_replyto_email_address'];
		$esr_subject 	= $row['esr_subject'];
		$esr_content 	= $row['esr_content'];
		$esr_plain_text	= $row['esr_plain_text'];
		$esr_enable		= $row['esr_enable'];

		$logic_email_enable = (int) $row['logic_email_enable'];

		$webhook_enable = (int) $row['webhook_enable'];
		

		//start sending notification email to admin ------------------------------------------
		if(!empty($esl_enable) && !empty($form_email)){
			//get parameters for the email
					
			//from name
			if(!empty($esl_from_name)){
				if(is_numeric($esl_from_name)){
					$admin_email_param['from_name'] = '{element_'.$esl_from_name.'}';
				}else{
					$admin_email_param['from_name'] = $esl_from_name;
				}
			}else{
				$admin_email_param['from_name'] = 'MachForm';
			}
			
			//from email address
			if(!empty($esl_from_email_address)){
				if(is_numeric($esl_from_email_address)){
					$admin_email_param['from_email'] = '{element_'.$esl_from_email_address.'}';
				}else{
					$admin_email_param['from_email'] = $esl_from_email_address;
				}
			}else{
				$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
				$admin_email_param['from_email'] = "no-reply@{$domain}";
			}

			//reply-to email address
			if(!empty($esl_replyto_email_address)){
				if(is_numeric($esl_replyto_email_address)){
					$admin_email_param['replyto_email'] = '{element_'.$esl_replyto_email_address.'}';
				}else{
					$admin_email_param['replyto_email'] = $esl_replyto_email_address;
				}
			}else{
				$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
				$admin_email_param['replyto_email'] = "no-reply@{$domain}";
			}
			
			//subject
			if(!empty($esl_subject)){
				$admin_email_param['subject'] = $esl_subject;
			}else{
				$admin_email_param['subject'] = '{form_name} [#{entry_no}]';
			}
			
			//content
			if(!empty($esl_content)){
				$admin_email_param['content'] = $esl_content;
			}else{
				$admin_email_param['content'] = '{entry_data}';
			}
			
			$admin_email_param['as_plain_text'] = $esl_plain_text;
			$admin_email_param['target_is_admin'] = true; 
			$admin_email_param['machform_base_path'] = $options['machform_path'];
			$admin_email_param['check_hook_file'] = true;
			 
			mf_send_notification($dbh,$form_id,$entry_id,$form_email,$admin_email_param);
    	
		}
		//end emailing notifications to admin ----------------------------------------------

		//start sending notification email to user ------------------------------------------
		if(!empty($esr_enable) && !empty($esr_email_address)){
			//get parameters for the email
			
			//to email 
			if(is_numeric($esr_email_address)){
				$esr_email_address = '{element_'.$esr_email_address.'}';
			}
					
			//from name
			if(!empty($esr_from_name)){
				if(is_numeric($esr_from_name)){
					$user_email_param['from_name'] = '{element_'.$esr_from_name.'}';
				}else{
					$user_email_param['from_name'] = $esr_from_name;
				}
			}else{
				$user_email_param['from_name'] = 'MachForm';
			}
			
			//from email address
			if(!empty($esr_from_email_address)){
				if(is_numeric($esr_from_email_address)){
					$user_email_param['from_email'] = '{element_'.$esr_from_email_address.'}';
				}else{
					$user_email_param['from_email'] = $esr_from_email_address;
				}
			}else{
				$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
				$user_email_param['from_email'] = "no-reply@{$domain}";
			}

			//reply-to email address
			if(!empty($esr_replyto_email_address)){
				if(is_numeric($esr_replyto_email_address)){
					$user_email_param['replyto_email'] = '{element_'.$esr_replyto_email_address.'}';
				}else{
					$user_email_param['replyto_email'] = $esr_replyto_email_address;
				}
			}else{
				$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
				$user_email_param['replyto_email'] = "no-reply@{$domain}";
			}
			
			//subject
			if(!empty($esr_subject)){
				$user_email_param['subject'] = $esr_subject;
			}else{
				$user_email_param['subject'] = '{form_name} - Receipt';
			}
			
			//content
			if(!empty($esr_content)){
				$user_email_param['content'] = $esr_content;
			}else{
				$user_email_param['content'] = '{entry_data}';
			}
			
			$user_email_param['as_plain_text'] = $esr_plain_text;
			$user_email_param['target_is_admin'] = false;
			$user_email_param['machform_base_path'] = $options['machform_path']; 
			
			mf_send_notification($dbh,$form_id,$entry_id,$esr_email_address,$user_email_param);
		}
		//end emailing notifications to user ----------------------------------------------

		//send all notifications triggered by email-logic
		if(!empty($logic_email_enable)){
			$logic_email_param = array();
			$logic_email_param['machform_base_path'] = $options['machform_path'];

			mf_send_logic_notifications($dbh,$form_id,$entry_id,$logic_email_param);
		}

		//send webhook notification
		if(!empty($webhook_enable)){
			mf_send_webhook_notification($dbh,$form_id,$entry_id,0);
		}
    }

    //send all notifications triggered by email-logic functionality
    function mf_send_logic_notifications($dbh,$form_id,$entry_id,$options=array()){
    	
    	$machform_base_path = $options['machform_base_path'];

    	//get all the rules from ap_email_logic table
    	$query = "SELECT 
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
						rule_id asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
				
		$email_logic_array = array();
		$i = 0;
		while($row = mf_do_fetch_result($sth)){
			$email_logic_array[$i]['rule_id'] 	   		= $row['rule_id'];
			$email_logic_array[$i]['rule_all_any'] 		= $row['rule_all_any'];
			$email_logic_array[$i]['target_email'] 		= $row['target_email'];
			$email_logic_array[$i]['template_name'] 	= $row['template_name'];
			$email_logic_array[$i]['custom_from_name'] 	= $row['custom_from_name'];
			$email_logic_array[$i]['custom_from_email'] = $row['custom_from_email'];
			$email_logic_array[$i]['custom_replyto_email'] = $row['custom_replyto_email'];
			$email_logic_array[$i]['custom_subject'] 	= $row['custom_subject'];
			$email_logic_array[$i]['custom_content'] 	= $row['custom_content'];
			$email_logic_array[$i]['custom_plain_text'] = $row['custom_plain_text'];
			$i++;
		}

		//evaluate the condition for each rule
		//if the condition true, send the email
		if(!empty($email_logic_array)){

			foreach ($email_logic_array as $value) {
				$target_rule_id = $value['rule_id'];
				$rule_all_any 	= $value['rule_all_any'];
				$target_email 	= $value['target_email'];
				$template_name	= $value['template_name'];

				$custom_from_name  	  = $value['custom_from_name'];
				$custom_from_email 	  = $value['custom_from_email'];
				$custom_replyto_email = $value['custom_replyto_email'];
				$custom_subject	   = $value['custom_subject'];
				$custom_content	   = $value['custom_content'];
				$custom_plain_text = (int) $value['custom_plain_text'];

				$current_rule_conditions_status = array();

				$query = "SELECT 
								element_name,
								rule_condition,
								rule_keyword 
							FROM 
								".MF_TABLE_PREFIX."email_logic_conditions 
						   WHERE 
						   		form_id = ? AND target_rule_id = ?";
				$params = array($form_id,$target_rule_id);
				
				$sth = mf_do_query($query,$params,$dbh);
				while($row = mf_do_fetch_result($sth)){
					
					$condition_params = array();
					$condition_params['form_id']		= $form_id;
					$condition_params['element_name'] 	= $row['element_name'];
					$condition_params['rule_condition'] = $row['rule_condition'];
					$condition_params['rule_keyword'] 	= $row['rule_keyword'];
					$condition_params['use_main_table'] = true;
					$condition_params['entry_id'] 		= $entry_id;  
					
					$current_rule_conditions_status[] = mf_get_condition_status_from_table($dbh,$condition_params);
				}
				
				if($rule_all_any == 'all'){
					if(in_array(false, $current_rule_conditions_status)){
						$all_conditions_status = false;
					}else{
						$all_conditions_status = true;
					}
				}else if($rule_all_any == 'any'){
					if(in_array(true, $current_rule_conditions_status)){
						$all_conditions_status = true;
					}else{
						$all_conditions_status = false;
					}
				}

				if($all_conditions_status === true && !empty($target_email)){

					//prepare target email address
					$exploded  = array();
					$to_emails = array();
					
					$exploded = explode(',', $target_email);
					foreach ($exploded as $email) {
						$email = trim($email);
						if(is_numeric($email)){
							$email = '{element_'.$email.'}';
						}

						$to_emails[] = $email;
					}
					$target_email = implode(',', $to_emails);

					//send the email
					if($template_name == 'notification' || $template_name == 'confirmation'){
						
						//get form properties data
						$query 	= "select 
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
										 esr_plain_text
								     from 
								     	 `".MF_TABLE_PREFIX."forms` 
								    where 
								    	 form_id=?";
						$params = array($form_id);
						
						$sth = mf_do_query($query,$params,$dbh);
						$row = mf_do_fetch_result($sth);
						
						$esl_from_name 	= $row['esl_from_name'];
						$esl_from_email_address 	= $row['esl_from_email_address'];
						$esl_replyto_email_address 	= $row['esl_replyto_email_address'];
						$esl_subject 	= $row['esl_subject'];
						$esl_content 	= $row['esl_content'];
						$esl_plain_text	= $row['esl_plain_text'];
						$esl_enable     = $row['esl_enable'];
		
						$esr_from_name 	= $row['esr_from_name'];
						$esr_from_email_address 	= $row['esr_from_email_address'];
						$esr_replyto_email_address 	= $row['esr_replyto_email_address'];
						$esr_subject 	= $row['esr_subject'];
						$esr_content 	= $row['esr_content'];
						$esr_plain_text	= $row['esr_plain_text'];
						$esr_enable		= $row['esr_enable'];

						if($template_name == 'notification'){
							$admin_email_param = array();

							//from name
							if(!empty($esl_from_name)){
								if(is_numeric($esl_from_name)){
									$admin_email_param['from_name'] = '{element_'.$esl_from_name.'}';
								}else{
									$admin_email_param['from_name'] = $esl_from_name;
								}
							}else{
								$admin_email_param['from_name'] = 'MachForm';
							}
							
							//from email address
							if(!empty($esl_from_email_address)){
								if(is_numeric($esl_from_email_address)){
									$admin_email_param['from_email'] = '{element_'.$esl_from_email_address.'}';
								}else{
									$admin_email_param['from_email'] = $esl_from_email_address;
								}
							}else{
								$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
								$admin_email_param['from_email'] = "no-reply@{$domain}";
							}

							//reply-to email address
							if(!empty($esl_replyto_email_address)){
								if(is_numeric($esl_replyto_email_address)){
									$admin_email_param['replyto_email'] = '{element_'.$esl_replyto_email_address.'}';
								}else{
									$admin_email_param['replyto_email'] = $esl_replyto_email_address;
								}
							}else{
								$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
								$admin_email_param['replyto_email'] = "no-reply@{$domain}";
							}
							
							//subject
							if(!empty($esl_subject)){
								$admin_email_param['subject'] = $esl_subject;
							}else{
								$admin_email_param['subject'] = '{form_name} [#{entry_no}]';
							}
							
							//content
							if(!empty($esl_content)){
								$admin_email_param['content'] = $esl_content;
							}else{
								$admin_email_param['content'] = '{entry_data}';
							}
							
							$admin_email_param['as_plain_text'] = $esl_plain_text;
							$admin_email_param['target_is_admin'] = true; 
							$admin_email_param['machform_base_path'] = $machform_base_path;
							$admin_email_param['check_hook_file'] = false;
							
 							mf_send_notification($dbh,$form_id,$entry_id,$target_email,$admin_email_param);
						}else if($template_name == 'confirmation'){
							$user_email_param = array();

							//from name
							if(!empty($esr_from_name)){
								if(is_numeric($esr_from_name)){
									$user_email_param['from_name'] = '{element_'.$esr_from_name.'}';
								}else{
									$user_email_param['from_name'] = $esr_from_name;
								}
							}else{
								$user_email_param['from_name'] = 'MachForm';
							}
							
							//from email address
							if(!empty($esr_from_email_address)){
								if(is_numeric($esr_from_email_address)){
									$user_email_param['from_email'] = '{element_'.$esr_from_email_address.'}';
								}else{
									$user_email_param['from_email'] = $esr_from_email_address;
								}
							}else{
								$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
								$user_email_param['from_email'] = "no-reply@{$domain}";
							}

							//reply-to email address
							if(!empty($esr_replyto_email_address)){
								if(is_numeric($esr_replyto_email_address)){
									$user_email_param['replyto_email'] = '{element_'.$esr_replyto_email_address.'}';
								}else{
									$user_email_param['replyto_email'] = $esr_replyto_email_address;
								}
							}else{
								$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
								$user_email_param['replyto_email'] = "no-reply@{$domain}";
							}
							
							//subject
							if(!empty($esr_subject)){
								$user_email_param['subject'] = $esr_subject;
							}else{
								$user_email_param['subject'] = '{form_name} - Receipt';
							}
							
							//content
							if(!empty($esr_content)){
								$user_email_param['content'] = $esr_content;
							}else{
								$user_email_param['content'] = '{entry_data}';
							}
							
							$user_email_param['as_plain_text'] = $esr_plain_text;
							$user_email_param['target_is_admin'] = false;
							$user_email_param['machform_base_path'] = $machform_base_path; 
							
							mf_send_notification($dbh,$form_id,$entry_id,$target_email,$user_email_param);
						}
					}else if($template_name == 'custom'){
						
						$admin_email_param = array();

						//from name
						if(!empty($custom_from_name)){
							if(is_numeric($custom_from_name)){
								$admin_email_param['from_name'] = '{element_'.$custom_from_name.'}';
							}else{
								$admin_email_param['from_name'] = $custom_from_name;
							}
						}else{
							$admin_email_param['from_name'] = 'MachForm';
						}
						
						//from email address
						if(!empty($custom_from_email)){
							if(is_numeric($custom_from_email)){
								$admin_email_param['from_email'] = '{element_'.$custom_from_email.'}';
							}else{
								$admin_email_param['from_email'] = $custom_from_email;
							}
						}else{
							$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
							$admin_email_param['from_email'] = "no-reply@{$domain}";
						}

						//reply-to email address
						if(!empty($custom_replyto_email)){
							if(is_numeric($custom_replyto_email)){
								$admin_email_param['replyto_email'] = '{element_'.$custom_replyto_email.'}';
							}else{
								$admin_email_param['replyto_email'] = $custom_replyto_email;
							}
						}else{
							$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);
							$admin_email_param['replyto_email'] = "no-reply@{$domain}";
						}
						
						//subject
						if(!empty($custom_subject)){
							$admin_email_param['subject'] = $custom_subject;
						}else{
							$admin_email_param['subject'] = '{form_name} [#{entry_no}]';
						}
						
						//content
						if(!empty($custom_content)){
							$admin_email_param['content'] = $custom_content;
						}else{
							$admin_email_param['content'] = '{entry_data}';
						}
						
						$admin_email_param['as_plain_text'] = $custom_plain_text;
						$admin_email_param['target_is_admin'] = true; 
						$admin_email_param['machform_base_path'] = $machform_base_path;
						$admin_email_param['check_hook_file'] = false;
						
						mf_send_notification($dbh,$form_id,$entry_id,$target_email,$admin_email_param);
					}
				}

			}

		}

		return true;

    }


    function mf_send_resume_link($dbh,$form_name,$form_resume_url,$resume_email){
    	global $mf_lang;

    	//get settings first
    	$mf_settings = mf_get_settings($dbh);
    	
		$subject = sprintf($mf_lang['resume_email_subject'],$form_name);
    	$email_content = sprintf($mf_lang['resume_email_content'],$form_name,$form_resume_url,$form_resume_url);
    	
    	//create the mail transport
		if(!empty($mf_settings['smtp_enable'])){
			$s_transport = Swift_SmtpTransport::newInstance($mf_settings['smtp_host'], $mf_settings['smtp_port']);
			
			if(!empty($mf_settings['smtp_secure'])){
				//port 465 for (SSL), while port 587 for (TLS)
				if($mf_settings['smtp_port'] == '587'){
					$s_transport->setEncryption('tls');
				}else{
					$s_transport->setEncryption('ssl');
				}
			}
			
			if(!empty($mf_settings['smtp_auth'])){
				$s_transport->setUsername($mf_settings['smtp_username']);
  				$s_transport->setPassword($mf_settings['smtp_password']);
			}
		}else{
			$s_transport = Swift_MailTransport::newInstance(); //use PHP mail() transport
		}
    	
    	//create mailer instance
		$s_mailer = Swift_Mailer::newInstance($s_transport);
		if(file_exists($mf_settings['upload_dir']."/form_{$form_id}/files")){
			Swift_Preferences::getInstance()->setCacheType('disk')->setTempDir($mf_settings['upload_dir']."/form_{$form_id}/files");
		}
		
		$from_name  = html_entity_decode($mf_settings['default_from_name'],ENT_QUOTES);
		$from_email = $mf_settings['default_from_email'];
		
		if(!empty($resume_email) && !empty($form_resume_url)){
			$s_message = Swift_Message::newInstance()
			->setCharset('utf-8')
			->setMaxLineLength(1000)
			->setSubject($subject)
			->setFrom(array($from_email => $from_name))
			->setSender($from_email)
			->setReturnPath($from_email)
			->setTo($resume_email)
			->setBody($email_content, 'text/html');

			//send the message
			$send_result = $s_mailer->send($s_message);
			if(empty($send_result)){
				echo "Error sending email!";
			}
		}
    	
    }
    
    function mf_send_login_info($dbh,$user_id,$password){
    	global $mf_lang;

    	//get settings first
    	$mf_settings = mf_get_settings($dbh);

    	//get user information
    	$query = "select user_fullname,user_email from ".MF_TABLE_PREFIX."users where user_id=? and `status`=1";
    	
    	$params = array($user_id);
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		$user_fullname = $row['user_fullname'];
		$user_email = $row['user_email'];
    	
		$subject = 'Your MachForm login information';
		$email_template =<<<EOT
Hello %s,

You can login to MachForm panel using the following information:

<b>URL:</b> %s

<b>Email:</b> %s
<b>Password:</b> %s

Thank you.
EOT;

		$email_template = nl2br($email_template);
    	$email_content = sprintf($email_template,$user_fullname,$mf_settings['base_url'],$user_email,$password);
    	
    	$subject = utf8_encode($subject);
    	
    	//create the mail transport
		if(!empty($mf_settings['smtp_enable'])){
			$s_transport = Swift_SmtpTransport::newInstance($mf_settings['smtp_host'], $mf_settings['smtp_port']);
			
			if(!empty($mf_settings['smtp_secure'])){
				//port 465 for (SSL), while port 587 for (TLS)
				if($mf_settings['smtp_port'] == '587'){
					$s_transport->setEncryption('tls');
				}else{
					$s_transport->setEncryption('ssl');
				}
			}
			
			if(!empty($mf_settings['smtp_auth'])){
				$s_transport->setUsername($mf_settings['smtp_username']);
  				$s_transport->setPassword($mf_settings['smtp_password']);
			}
		}else{
			$s_transport = Swift_MailTransport::newInstance(); //use PHP mail() transport
		}
    	
    	//create mailer instance
		$s_mailer = Swift_Mailer::newInstance($s_transport);
		if(file_exists($mf_settings['upload_dir']."/form_{$form_id}/files") && is_writable($mf_settings['upload_dir']."/form_{$form_id}/files")){
			Swift_Preferences::getInstance()->setCacheType('disk')->setTempDir($mf_settings['upload_dir']."/form_{$form_id}/files");
		}else{
			Swift_Preferences::getInstance()->setCacheType('array');
		}
		
		$from_name  = html_entity_decode($mf_settings['default_from_name'],ENT_QUOTES);
		$from_email = $mf_settings['default_from_email'];
		
		if(!empty($user_email)){
			$s_message = Swift_Message::newInstance()
			->setCharset('utf-8')
			->setMaxLineLength(1000)
			->setSubject($subject)
			->setFrom(array($from_email => $from_name))
			->setSender($from_email)
			->setReturnPath($from_email)
			->setTo($user_email)
			->setBody($email_content, 'text/html');

			//send the message
			$send_result = $s_mailer->send($s_message);
			if(empty($send_result)){
				echo "Error sending email!";
			}
		}
    	
    }

    function mf_get_ssl_suffix(){
    	if(!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')){
			$ssl_suffix = 's';
		}else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
            $ssl_suffix = 's';
        }else if (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] == 'on'){
            $ssl_suffix = 's';
        }else{
			$ssl_suffix = '';
		}
		
		return $ssl_suffix;
    }
    
    function mf_get_dirname($path){
    	$current_dir = dirname($path);
    	
    	if($current_dir == "/" || $current_dir == "\\"){
			$current_dir = '';
		}
		
		return $current_dir;
    }
    
    function mf_get_settings($dbh){
    	$query = "SELECT * FROM ".MF_TABLE_PREFIX."settings";
    	$sth = mf_do_query($query,array(),$dbh);
    	$row = mf_do_fetch_result($sth);
    	return $row;
    }
    
    function mf_format_bytes($bytes) {
		if ($bytes < 1024) return $bytes.' B';
	   	elseif ($bytes < 1048576) return round($bytes / 1024, 2).' KB';
	   	elseif ($bytes < 1073741824) return round($bytes / 1048576, 2).' MB';
	   	elseif ($bytes < 1099511627776) return round($bytes / 1073741824, 2).' GB';
	   	else return round($bytes / 1099511627776, 2).' TB';
	}
	
	function mf_trim_value(&$value){ 
    	$value = trim($value); 
	}
	
	//generate the javascript code for conditional logic
	function mf_get_logic_javascript($dbh,$form_id,$page_number){

		$form_id = (int) $form_id;

		//get the target elements for the current page
		$query = "SELECT 
					A.element_id,
					A.rule_show_hide,
					A.rule_all_any,
					B.element_title,
					B.element_page_number 
				FROM 
					".MF_TABLE_PREFIX."field_logic_elements A LEFT JOIN ".MF_TABLE_PREFIX."form_elements B
				  ON 
				  	A.form_id = B.form_id and A.element_id=B.element_id and B.element_status = 1
			   WHERE
					A.form_id = ? and B.element_page_number = ?
			ORDER BY 
					B.element_position asc";
		$params = array($form_id,$page_number);
		$sth = mf_do_query($query,$params,$dbh);

		$logic_elements_array = array();
		
		while($row = mf_do_fetch_result($sth)){
			$element_id = (int) $row['element_id'];

			$logic_elements_array[$element_id]['element_title']  = $row['element_title'];
			$logic_elements_array[$element_id]['rule_show_hide'] = $row['rule_show_hide'];
			$logic_elements_array[$element_id]['rule_all_any']   = $row['rule_all_any'];
		}

		//get the conditions array
		$query = "SELECT 
						A.target_element_id,
						A.element_name,
						A.rule_condition,
						A.rule_keyword,
						trim(leading 'element_' from substring_index(A.element_name,'_',2)) as condition_element_id,
						(select 
							   B.element_page_number 
						   from 
						   	   ".MF_TABLE_PREFIX."form_elements B 
						  where 
						  		form_id=A.form_id and 
						  		element_id=condition_element_id
						) condition_element_page_number,
						(select 
							   C.element_type 
						   from 
						   	   ".MF_TABLE_PREFIX."form_elements C 
						  where 
						  		form_id=A.form_id and 
						  		element_id=condition_element_id
						) condition_element_type
					FROM 
						".MF_TABLE_PREFIX."field_logic_conditions A 
				   WHERE
						A.form_id = ?
				   ORDER by A.alc_id";
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

			$logic_conditions_array[$target_element_id][$i]['element_name']  		= $row['element_name'];
			$logic_conditions_array[$target_element_id][$i]['element_type']  		= $row['condition_element_type'];
			$logic_conditions_array[$target_element_id][$i]['element_page_number'] 	= (int) $row['condition_element_page_number'];
			$logic_conditions_array[$target_element_id][$i]['rule_condition'] 		= $row['rule_condition'];
			$logic_conditions_array[$target_element_id][$i]['rule_keyword']   		= $row['rule_keyword'];


			$prev_element_id = $target_element_id;
			$i++;
		}

		//build mf_handler_xx() function for each element
		$mf_handler_code = '';
		$mf_bind_code = '';
		$mf_initialize_code = '';

		foreach ($logic_elements_array as $element_id => $value) {
			$rule_show_hide = $value['rule_show_hide'];
			$rule_all_any   = $value['rule_all_any'];

			$current_handler_conditions_array = array();

			$mf_handler_code .= "\n"."function mf_handler_{$element_id}(){"."\n"; //start mf_handler_xx
			/************************************************************************************/
			
			$target_element_conditions = $logic_conditions_array[$element_id];

			$unique_field_suffix_id = 0;

			//initialize the condition status for any other elements which is not within this page
			//store the status into a variable
			foreach ($target_element_conditions as $value) {
				if($value['element_page_number'] == $page_number){
					continue;
				}

				$unique_field_suffix_id++;

				$current_handler_conditions_array[] = 'condition_'.$value['element_name'].'_'.$unique_field_suffix_id;

				$condition_params = array();
				$condition_params['form_id']		= $form_id;
				$condition_params['element_name'] 	= $value['element_name'];
				$condition_params['rule_condition'] = $value['rule_condition'];
				$condition_params['rule_keyword'] 	= $value['rule_keyword'];

				$condition_status = mf_get_condition_status_from_table($dbh,$condition_params);
				
				if($condition_status === true){
					$condition_status_value = 'true';
				}else{
					$condition_status_value = 'false';
				}

				$mf_handler_code .= "\t"."var condition_{$value['element_name']}_{$unique_field_suffix_id} = {$condition_status_value};"."\n";
			}

			$mf_handler_code .= "\n";

			//build the conditions for the current element
			
			foreach ($target_element_conditions as $value) {
				$unique_field_suffix_id++;

				//skip field which doesn't belong current page
				if($value['element_page_number'] != $page_number){
					continue;
				}

				//for checkbox with other, we need to replace the id
				if($value['element_type'] == 'checkbox'){
					$checkbox_has_other = false;

					if(substr($value['element_name'], -5) == 'other'){
						$value['element_name'] = str_replace('_other', '_0', $value['element_name']);
						$checkbox_has_other = true;
					}
				}

				

				$condition_params = array();
				$condition_params['form_id']		= $form_id;
				$condition_params['element_name'] 	= $value['element_name'];
				$condition_params['rule_condition'] = $value['rule_condition'];
				$condition_params['rule_keyword'] 	= $value['rule_keyword'];

				
				//we need to add unique suffix into the element name
				//so that we can use the same field multiple times to build a rule
				$condition_params['element_name'] = $value['element_name'].'_'.$unique_field_suffix_id;
				$current_handler_conditions_array[] = 'condition_'.$value['element_name'].'_'.$unique_field_suffix_id;
				

				$mf_handler_code .= mf_get_condition_javascript($dbh,$condition_params);

				//build the bind code
				if($value['element_type'] == 'radio'){
					$mf_bind_code .= "\$('input[name={$value['element_name']}]').bind('change click', function() {\n";
				}else if($value['element_type'] == 'time'){
					$mf_bind_code .= "\$('#{$value['element_name']}_1,#{$value['element_name']}_2,#{$value['element_name']}_3,#{$value['element_name']}_4').bind('keyup mouseout change click', function() {\n";
				}else if($value['element_type'] == 'money'){
					$mf_bind_code .= "\$('#{$value['element_name']},#{$value['element_name']}_1,#{$value['element_name']}_2').bind('keyup mouseout change click', function() {\n";
				}else if($value['element_type'] == 'matrix'){
					
					$exploded = array();
					$exploded = explode('_',$value['element_name']);

					$matrix_element_id = (int) $exploded[1];
					//we only need to bind the event to the parent element_id of the matrix
					$query = "select element_matrix_parent_id from ".MF_TABLE_PREFIX."form_elements where element_id = ? and form_id = ? and element_status = 1";
					
					$params = array($matrix_element_id,$form_id);
					$sth 	= mf_do_query($query,$params,$dbh);
					$row 	= mf_do_fetch_result($sth);
					if(!empty($row['element_matrix_parent_id'])){
						$matrix_element_id = $row['element_matrix_parent_id'];
					}

					
					$mf_bind_code .= "\$('#li_{$matrix_element_id} :input').bind('change click', function() {\n";
				}else if($value['element_type'] == 'checkbox'){
					if($checkbox_has_other){
						$exploded = array();
						$exploded = explode('_', $value['element_name']);

						$mf_bind_code .= "\$('#{$value['element_name']},#element_{$exploded[1]}_other').bind('keyup mouseout change click', function() {\n";
					}else{
						$mf_bind_code .= "\$('#{$value['element_name']}').bind('keyup mouseout change click', function() {\n";
					}
				}else if($value['element_type'] == 'select'){
					$mf_bind_code .= "\$('#{$value['element_name']}').bind('keyup change', function() {\n";
				}else if($value['element_type'] == 'date' || $value['element_type'] == 'europe_date'){
					$mf_bind_code .= "\$('#{$value['element_name']}_1,#{$value['element_name']}_2,#{$value['element_name']}_3').bind('keyup mouseout change click', function() {\n";
				}else if($value['element_type'] == 'phone'){
					$mf_bind_code .= "\$('#{$value['element_name']}_1,#{$value['element_name']}_2,#{$value['element_name']}_3').bind('keyup mouseout change click', function() {\n";
				}else{
					$mf_bind_code .= "\$('#{$value['element_name']}').bind('keyup mouseout change click', function() {\n";
				}
				
				$mf_bind_code .= "\tmf_handler_{$element_id}();\n";
				$mf_bind_code .= "});\n";
			}	
			
			//evaluate all conditions
			if($rule_all_any == 'all'){
				$logic_operator = ' && ';
			}else{
				$logic_operator = ' || ';
			}

			if($rule_show_hide == 'show'){
				$action_code_primary 	= "\$('#li_{$element_id}').show();";
				$action_code_secondary  = "\$('#li_{$element_id}').hide();";
			}else if($rule_show_hide == 'hide'){
				$action_code_primary 	= "\$('#li_{$element_id}').hide();";
				$action_code_secondary  = "\$('#li_{$element_id}').show();";
			}

			$current_handler_conditions_joined = implode($logic_operator, $current_handler_conditions_array);
			$mf_handler_code .= "\tif({$current_handler_conditions_joined}){\n";
			$mf_handler_code .= "\t\t{$action_code_primary}\n";
			$mf_handler_code .= "\t}else{\n";
			$mf_handler_code .= "\t\t{$action_code_secondary}\n";
			$mf_handler_code .= "\t}\n\n";

			//postMessage to adjust the height of the iframe
			$mf_handler_code .= "\tif($(\"html\").hasClass(\"embed\")){\n";
			$mf_handler_code .= "\t\t$.postMessage({mf_iframe_height: $('body').outerHeight(true)}, '*', parent );\n";
			$mf_handler_code .=	"\t}\n";

			/************************************************************************************/
			$mf_handler_code .= "}"."\n"; //end mf_handler_xx

			$mf_initialize_code .= "mf_handler_{$element_id}();\n";
		}


		$javascript_code = <<<EOT
<script type="text/javascript">
$(function(){

{$mf_handler_code}
{$mf_bind_code}
{$mf_initialize_code}

});
</script>
EOT;
	
		return $javascript_code;
	}
	
	function mf_get_condition_javascript($dbh,$condition_params){
		
		$form_id 		= (int) $condition_params['form_id'];
		$element_name 	= $condition_params['element_name']; //this could be 'element_x_y' or 'element_x_x_y', where 'y' is just unique field suffix
		$rule_condition = $condition_params['rule_condition'];
		
		if(function_exists('mb_strtolower')){
			$rule_keyword 	= addslashes(mb_strtolower($condition_params['rule_keyword'],'UTF-8')); //keyword is case insensitive
		}else{
			$rule_keyword 	= addslashes(strtolower($condition_params['rule_keyword'])); //keyword is case insensitive
		}

		$exploded = explode('_', $element_name);
		$element_id = (int) $exploded[1];

		//get the element properties of the current element id
		$query 	= "select 
						 element_type,
						 element_time_showsecond,
						 element_time_24hour,
						 element_constraint,
						 element_matrix_parent_id,
						 element_matrix_allow_multiselect 
					 from 
					 	 ".MF_TABLE_PREFIX."form_elements 
					where 
						 form_id = ? and element_id = ?";
		$params = array($form_id,$element_id);
		$sth 	= mf_do_query($query,$params,$dbh);
		$row 	= mf_do_fetch_result($sth);

		$element_type 			  = $row['element_type'];
		$element_time_showsecond  = (int) $row['element_time_showsecond'];
		$element_time_24hour	  = (int) $row['element_time_24hour'];
		$element_constraint		  = $row['element_constraint'];
		$element_matrix_parent_id = (int) $row['element_matrix_parent_id'];
		$element_matrix_allow_multiselect = (int) $row['element_matrix_allow_multiselect'];

		//if this is matrix field, we need to determine wether this is matrix choice or matrix checkboxes
		if($element_type == 'matrix'){
			if(empty($element_matrix_parent_id)){
				if(!empty($element_matrix_allow_multiselect)){
					$element_type = 'matrix_checkbox';
				}else{
					$element_type = 'matrix_radio';
				}
			}else{
				//this is a child row of a matrix, get the parent id first and check the status of the multiselect option
				$query = "select element_matrix_allow_multiselect from ".MF_TABLE_PREFIX."form_elements where form_id = ? and element_id = ?";
				$params = array($form_id,$element_matrix_parent_id);
				$sth 	= mf_do_query($query,$params,$dbh);
				$row 	= mf_do_fetch_result($sth);

				if(!empty($row['element_matrix_allow_multiselect'])){
					$element_type = 'matrix_checkbox';
				}else{
					$element_type = 'matrix_radio';
				}
			}
		}


		$condition_javascript = '';
		
		$exploded = array();
		$exploded = explode('_', $element_name);

		if(count($exploded) == 3){
			$element_name_clean = 'element_'.$exploded[1]; //element name, without the unique suffix
		}else{
			$element_name_clean = 'element_'.$exploded[1].'_'.$exploded[2]; //element name, without the unique suffix
		}
		
		if(in_array($element_type, array('text','textarea','simple_name','name','simple_name_wmiddle','name_wmiddle','address','simple_phone','url','email'))){
			$condition_javascript .= "var {$element_name} = \$('#{$element_name_clean}').val().toLowerCase();"."\n";

			if($rule_condition == 'is'){
				$condition_javascript .= "\tif({$element_name} == '{$rule_keyword}'){"."\n";	
			}else if($rule_condition == 'is_not'){
				$condition_javascript .= "\tif({$element_name} != '{$rule_keyword}'){"."\n";
			}else if($rule_condition == 'begins_with'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == 0){"."\n";
			}else if($rule_condition == 'ends_with'){
				$condition_javascript .= "\tvar keyword_{$element_name} = '{$rule_keyword}';\n";
				$condition_javascript .= "\tif({$element_name}.indexOf(keyword_{$element_name},{$element_name}.length - keyword_{$element_name}.length) != -1){"."\n";
			}else if($rule_condition == 'contains'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') >= 0){"."\n";
			}else if($rule_condition == 'not_contain'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == -1){"."\n";
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";

		}else if($element_type == 'radio'){
			$condition_javascript .= "var {$element_name} = \$('input[name={$element_name_clean}]:checked').next().text().toLowerCase();"."\n";

			if($rule_condition == 'is'){
				$condition_javascript .= "\tif({$element_name} == '{$rule_keyword}'){"."\n";	
			}else if($rule_condition == 'is_not'){
				$condition_javascript .= "\tif({$element_name} != '{$rule_keyword}'){"."\n";
			}else if($rule_condition == 'begins_with'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == 0){"."\n";
			}else if($rule_condition == 'ends_with'){
				$condition_javascript .= "\tvar keyword_{$element_name} = '{$rule_keyword}';\n";
				$condition_javascript .= "\tif({$element_name}.indexOf(keyword_{$element_name},{$element_name}.length - keyword_{$element_name}.length) != -1){"."\n";
			}else if($rule_condition == 'contains'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') >= 0){"."\n";
			}else if($rule_condition == 'not_contain'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == -1){"."\n";
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";
		}else if($element_type == 'time'){
			
			//there are few variants of the the time field, get the specific type
			if(!empty($element_time_showsecond) && !empty($element_time_24hour)){
				$element_type = 'time_showsecond24hour';
			}else if(!empty($element_time_showsecond) && empty($element_time_24hour)){
				$element_type = 'time_showsecond';
			}else if(empty($element_time_showsecond) && !empty($element_time_24hour)){
				$element_type = 'time_24hour';
			}

			$exploded = array();
			$exploded = explode(':', $rule_keyword); //rule keyword format -> HH:MM:SS:AM

			if($element_type == 'time'){
				$time_keyword 		   = "Date.parse('01/01/2012 {$exploded[0]}:{$exploded[1]} {$exploded[3]}')";
				$condition_javascript .= "var {$element_name}_timestring = $('#{$element_name_clean}_1').val() + ':' + $('#{$element_name_clean}_2').val() + ' ' + $('#{$element_name_clean}_4').val();\n";
			}else if($element_type == 'time_showsecond'){
				$time_keyword 		   = "Date.parse('01/01/2012 {$exploded[0]}:{$exploded[1]}:{$exploded[2]} {$exploded[3]}')";
				$condition_javascript .= "var {$element_name}_timestring = $('#{$element_name_clean}_1').val() + ':' + $('#{$element_name_clean}_2').val() + ':' + $('#{$element_name_clean}_3').val() + ' ' + $('#{$element_name_clean}_4').val();\n";
			}else if($element_type == 'time_24hour'){
				$time_keyword 		   = "Date.parse('01/01/2012 {$exploded[0]}:{$exploded[1]}')";
				$condition_javascript .= "var {$element_name}_timestring = $('#{$element_name_clean}_1').val() + ':' + $('#{$element_name_clean}_2').val();\n";
			}else if($element_type == 'time_showsecond24hour'){
				$time_keyword 		   = "Date.parse('01/01/2012 {$exploded[0]}:{$exploded[1]}:{$exploded[2]}')";
				$condition_javascript .= "var {$element_name}_timestring = $('#{$element_name_clean}_1').val() + ':' + $('#{$element_name_clean}_2').val() + ':' + $('#{$element_name_clean}_3').val();\n";
			}

			$condition_javascript .= "\tvar {$element_name} = Date.parse('01/01/2012 ' + {$element_name}_timestring);\n\n"; 

			if($rule_condition == 'is'){
				$condition_javascript .= "\tif({$time_keyword} == {$element_name}){"."\n";	
			}else if($rule_condition == 'is_before'){
				$condition_javascript .= "\tif({$time_keyword} > {$element_name}){"."\n";	
			}else if($rule_condition == 'is_after'){
				$condition_javascript .= "\tif({$time_keyword} < {$element_name}){"."\n";	
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";
		}else if($element_type == 'money'){

			if($element_constraint == 'yen'){ //yen only have one field
				$condition_javascript .= "var {$element_name} = \$('#{$element_name_clean}').val();"."\n";
			}else{
				$condition_javascript .= "var {$element_name} = \$('#{$element_name_clean}_1').val() + '.' + \$('#{$element_name_clean}_2').val();"."\n";
			}
			

			if($rule_condition == 'is'){
				$condition_javascript .= "\tif(parseFloat({$element_name}) == parseFloat('{$rule_keyword}')){"."\n";	
			}else if($rule_condition == 'less_than'){
				$condition_javascript .= "\tif(parseFloat({$element_name}) < parseFloat('{$rule_keyword}')){"."\n";
			}else if($rule_condition == 'greater_than'){
				$condition_javascript .= "\tif(parseFloat({$element_name}) > parseFloat('{$rule_keyword}')){"."\n";
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";

		}else if($element_type == 'matrix_radio'){

			$condition_javascript .= "var selected_choice_{$element_id} = \$('#mr_{$element_id} input[name=element_{$element_id}]:checked').index('#mr_{$element_id} input[name=element_{$element_id}]') + 1;\n";
			$condition_javascript .= "\tvar {$element_name} = \$('#mr_{$element_id}').parentsUntil('li').filter('table').children('thead').children().children().eq(selected_choice_{$element_id}).text().toLowerCase();\n\n";

			if($rule_condition == 'is'){
				$condition_javascript .= "\tif({$element_name} == '{$rule_keyword}'){"."\n";	
			}else if($rule_condition == 'is_not'){
				$condition_javascript .= "\tif({$element_name} != '{$rule_keyword}'){"."\n";
			}else if($rule_condition == 'begins_with'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == 0){"."\n";
			}else if($rule_condition == 'ends_with'){
				$condition_javascript .= "\tvar keyword_{$element_name} = '{$rule_keyword}';\n";
				$condition_javascript .= "\tif({$element_name}.indexOf(keyword_{$element_name},{$element_name}.length - keyword_{$element_name}.length) != -1){"."\n";
			}else if($rule_condition == 'contains'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') >= 0){"."\n";
			}else if($rule_condition == 'not_contain'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == -1){"."\n";
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";
		}else if($element_type == 'matrix_checkbox' || $element_type == 'checkbox'){

			$condition_javascript .= "var {$element_name} = \$('#{$element_name_clean}').prop('checked');"."\n";

			if($rule_condition == 'is_one'){
				$condition_javascript .= "\tif({$element_name} == true){"."\n";	
			}else if($rule_condition == 'is_zero'){
				$condition_javascript .= "\tif({$element_name} == false){"."\n";
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";
		}else if($element_type == 'number'){

			$condition_javascript .= "var {$element_name} = \$('#{$element_name_clean}').val();"."\n";
			
			if($rule_condition == 'is'){
				$condition_javascript .= "\tif(parseFloat({$element_name}) == parseFloat('{$rule_keyword}')){"."\n";	
			}else if($rule_condition == 'less_than'){
				$condition_javascript .= "\tif(parseFloat({$element_name}) < parseFloat('{$rule_keyword}')){"."\n";
			}else if($rule_condition == 'greater_than'){
				$condition_javascript .= "\tif(parseFloat({$element_name}) > parseFloat('{$rule_keyword}')){"."\n";
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";

		}else if($element_type == 'select'){
			$condition_javascript .= "var {$element_name} = \$('#{$element_name_clean} option:selected').text().toLowerCase();"."\n";

			if($rule_condition == 'is'){
				$condition_javascript .= "\tif({$element_name} == '{$rule_keyword}'){"."\n";	
			}else if($rule_condition == 'is_not'){
				$condition_javascript .= "\tif({$element_name} != '{$rule_keyword}'){"."\n";
			}else if($rule_condition == 'begins_with'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == 0){"."\n";
			}else if($rule_condition == 'ends_with'){
				$condition_javascript .= "\tvar keyword_{$element_name} = '{$rule_keyword}';\n";
				$condition_javascript .= "\tif({$element_name}.indexOf(keyword_{$element_name},{$element_name}.length - keyword_{$element_name}.length) != -1){"."\n";
			}else if($rule_condition == 'contains'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') >= 0){"."\n";
			}else if($rule_condition == 'not_contain'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == -1){"."\n";
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";

		}else if($element_type == 'date' || $element_type == 'europe_date'){
			
			$date_keyword 		   = "Date.parse('{$rule_keyword}')";

			if($element_type == 'date'){
				$condition_javascript .= "var {$element_name}_datestring = $('#{$element_name_clean}_1').val() + '/' + $('#{$element_name_clean}_2').val() + '/' + $('#{$element_name_clean}_3').val();\n";
			}else if ($element_type == 'europe_date') {
				$condition_javascript .= "var {$element_name}_datestring = $('#{$element_name_clean}_2').val() + '/' + $('#{$element_name_clean}_1').val() + '/' + $('#{$element_name_clean}_3').val();\n";
			}
			$condition_javascript .= "\tvar {$element_name} = Date.parse({$element_name}_datestring);\n\n"; 

			if($rule_condition == 'is'){
				$condition_javascript .= "\tif({$date_keyword} == {$element_name}){"."\n";	
			}else if($rule_condition == 'is_before'){
				$condition_javascript .= "\tif({$date_keyword} > {$element_name}){"."\n";	
			}else if($rule_condition == 'is_after'){
				$condition_javascript .= "\tif({$date_keyword} < {$element_name}){"."\n";	
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";
		}else if($element_type == 'phone'){
			$condition_javascript .= "var {$element_name} = \$('#{$element_name_clean}_1').val() + \$('#{$element_name_clean}_2').val() + \$('#{$element_name_clean}_3').val();"."\n";

			if($rule_condition == 'is'){
				$condition_javascript .= "\tif({$element_name} == '{$rule_keyword}'){"."\n";	
			}else if($rule_condition == 'is_not'){
				$condition_javascript .= "\tif({$element_name} != '{$rule_keyword}'){"."\n";
			}else if($rule_condition == 'begins_with'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == 0){"."\n";
			}else if($rule_condition == 'ends_with'){
				$condition_javascript .= "\tvar keyword_{$element_name} = '{$rule_keyword}';\n";
				$condition_javascript .= "\tif({$element_name}.indexOf(keyword_{$element_name},{$element_name}.length - keyword_{$element_name}.length) != -1){"."\n";
			}else if($rule_condition == 'contains'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') >= 0){"."\n";
			}else if($rule_condition == 'not_contain'){
				$condition_javascript .= "\tif({$element_name}.indexOf('{$rule_keyword}') == -1){"."\n";
			}

			$condition_javascript .= "\t\tvar condition_{$element_name} = true;\n\t}else{\n\t\tvar condition_{$element_name} = false; \n\t}\n";

		}

		return "\t".$condition_javascript."\n";

	}
    
    //get the status of a condition where the field is not coming from the current page on a multipage form
    //this function get the user input from the review table (default) or main form table
    function mf_get_condition_status_from_table($dbh,$condition_params){

    	$form_id 		= (int) $condition_params['form_id'];
		$element_name 	= $condition_params['element_name']; //this could be 'element_x' or 'element_x_x'
		$rule_condition = $condition_params['rule_condition'];
		$rule_keyword 	= addslashes(strtolower($condition_params['rule_keyword'])); //keyword is case insensitive

		$session_id 	= session_id();

		if($condition_params['use_main_table'] === true){
			$table_suffix = '';
			$record_name  = 'id';
			$record_id    = $condition_params['entry_id'];
		}else{
			$table_suffix = '_review';
			$record_name  = 'session_id';
			$record_id    = $session_id;
		}

		$condition_status = false; //the default status if false

		$exploded = explode('_', $element_name);
		$element_id = (int) $exploded[1];

		//get the element properties of the current element id
		$query 	= "select 
						 element_type,
						 element_choice_has_other,
						 element_time_showsecond,
						 element_time_24hour,
						 element_matrix_parent_id,
						 element_matrix_allow_multiselect
					 from 
					 	 ".MF_TABLE_PREFIX."form_elements 
					where 
						 form_id = ? and element_id = ?";
		$params = array($form_id,$element_id);
		$sth 	= mf_do_query($query,$params,$dbh);
		$row 	= mf_do_fetch_result($sth);

		$element_type 			  = $row['element_type'];
		$element_choice_has_other = $row['element_choice_has_other'];
		$element_time_showsecond  = (int) $row['element_time_showsecond'];
		$element_time_24hour	  = (int) $row['element_time_24hour'];
		$element_matrix_parent_id = (int) $row['element_matrix_parent_id'];
		$element_matrix_allow_multiselect = (int) $row['element_matrix_allow_multiselect'];

		//if this is matrix field, we need to determine wether this is matrix choice or matrix checkboxes
		if($element_type == 'matrix'){
			if(empty($element_matrix_parent_id)){
				if(!empty($element_matrix_allow_multiselect)){
					$element_type = 'matrix_checkbox';
				}else{
					$element_type = 'matrix_radio';
				}
			}else{
				//this is a child row of a matrix, get the parent id first and check the status of the multiselect option
				$query = "select element_matrix_allow_multiselect from ".MF_TABLE_PREFIX."form_elements where form_id = ? and element_id = ?";
				$params = array($form_id,$element_matrix_parent_id);
				$sth 	= mf_do_query($query,$params,$dbh);
				$row 	= mf_do_fetch_result($sth);

				if(!empty($row['element_matrix_allow_multiselect'])){
					$element_type = 'matrix_checkbox';
				}else{
					$element_type = 'matrix_radio';
				}
			}
		}

		if(in_array($element_type, array('text','textarea','simple_name','name','simple_name_wmiddle','name_wmiddle','address','phone','simple_phone','email','url'))){
			
			if($rule_condition == 'is'){
				$where_operand = '=';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'is_not'){
				$where_operand = '<>';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'begins_with'){
				$where_operand = 'LIKE';
				$where_keyword = "'{$rule_keyword}%'";
			}else if($rule_condition == 'ends_with'){
				$where_operand = 'LIKE';
				$where_keyword = "'%{$rule_keyword}'";
			}else if($rule_condition == 'contains'){
				$where_operand = 'LIKE';
				$where_keyword = "'%{$rule_keyword}%'";
			}else if($rule_condition == 'not_contain'){
				$where_operand = 'NOT LIKE';
				$where_keyword = "'%{$rule_keyword}%'";
			}

			//get the entered value on the table
			$query = "select 
							count(`id`) total_row 
						from 
							".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} 
					   where 
					   		`{$element_name}` {$where_operand} {$where_keyword} 
					   		and `{$record_name}` = ?";
			$params = array($record_id);
			$sth 	= mf_do_query($query,$params,$dbh);
			$row 	= mf_do_fetch_result($sth);

			if(!empty($row['total_row'])){
				$condition_status = true;
			}

		}else if($element_type == 'radio' || $element_type == 'select'){
			
			if($rule_condition == 'is'){
				$where_operand = '=';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'is_not'){
				$where_operand = '<>';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'begins_with'){
				$where_operand = 'LIKE';
				$where_keyword = "'{$rule_keyword}%'";
			}else if($rule_condition == 'ends_with'){
				$where_operand = 'LIKE';
				$where_keyword = "'%{$rule_keyword}'";
			}else if($rule_condition == 'contains'){
				$where_operand = 'LIKE';
				$where_keyword = "'%{$rule_keyword}%'";
			}else if($rule_condition == 'not_contain'){
				$where_operand = 'NOT LIKE';
				$where_keyword = "'%{$rule_keyword}%'";
			}

			//get the entered value on the table
			$query = "SELECT 
							count(B.element_title) total_row 
					    FROM(
							 SELECT 
								   A.`{$element_name}`,
								   (select 
								   		  `option` 
								   	  from 
								   	  	  ".MF_TABLE_PREFIX."element_options 
								   	 where 
								   	 	  form_id = ? and 
								   	 	  element_id = ? and 
								   	 	  option_id = A.element_{$element_id} and 
								   	 	  live = 1) element_title
							   FROM 
							  	   ".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} A
							  WHERE 
							  	   `{$record_name}` = ?
							) B 
					   WHERE 
					   		B.element_title {$where_operand} {$where_keyword}";

			$params = array($form_id,$element_id,$record_id);
			$sth 	= mf_do_query($query,$params,$dbh);
			$row 	= mf_do_fetch_result($sth);

			if(!empty($row['total_row'])){
				$condition_status = true;
			}

			//if the choice field has 'other' and the condition is still false, we need to check into the 'other' field
			if(!empty($element_choice_has_other) && $condition_status === false){
				$query = "SELECT 
							count(B.element_title) total_row 
					    FROM(
							 SELECT 
								   A.`element_{$element_id}`,
								   (select element_choice_other_label from ".MF_TABLE_PREFIX."form_elements where form_id = ? and element_id = ?) element_title	 
							   FROM 
								   ".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} A
							  WHERE
								   A.`{$record_name}` = ? and
								   A.element_{$element_id} = 0 and
								   A.element_{$element_id}_other is not null and
								   A.element_{$element_id}_other <> ''
							) B 
					   WHERE 
					   		B.element_title {$where_operand} {$where_keyword}";

				$params = array($form_id,$element_id,$record_id);
				$sth 	= mf_do_query($query,$params,$dbh);
				$row 	= mf_do_fetch_result($sth);

				if(!empty($row['total_row'])){
					$condition_status = true;
				}

			}

		}else if($element_type == 'time'){

			//there are few variants of the the time field, get the specific type
			if(!empty($element_time_showsecond) && !empty($element_time_24hour)){
				$element_type = 'time_showsecond24hour';
			}else if(!empty($element_time_showsecond) && empty($element_time_24hour)){
				$element_type = 'time_showsecond';
			}else if(empty($element_time_showsecond) && !empty($element_time_24hour)){
				$element_type = 'time_24hour';
			}

			$exploded = array();
			$exploded = explode(':', $rule_keyword); //rule keyword format -> HH:MM:SS:AM

			if($element_type == 'time'){
				$rule_keyword = date("H:i:s",strtotime("{$exploded[0]}:{$exploded[1]}:00 {$exploded[3]}"));
			}else if($element_type == 'time_showsecond'){
				$rule_keyword = date("H:i:s",strtotime("{$exploded[0]}:{$exploded[1]}:{$exploded[2]} {$exploded[3]}"));
			}else if($element_type == 'time_24hour'){
				$rule_keyword = date("H:i:s",strtotime("{$exploded[0]}:{$exploded[1]}:00"));
			}else if($element_type == 'time_showsecond24hour'){
				$rule_keyword = date("H:i:s",strtotime("{$exploded[0]}:{$exploded[1]}:{$exploded[2]}"));
			}

			if($rule_condition == 'is'){
				$where_operand = '=';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'is_before'){
				$where_operand = '<';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'is_after'){
				$where_operand = '>';
				$where_keyword = "'{$rule_keyword}'";
			}

			//get the entered value on the table
			$query = "select 
							count(`id`) total_row 
						from 
							".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} 
					   where 
					   		time(`{$element_name}`) {$where_operand} {$where_keyword} 
					   		and `{$record_name}` = ?";
			
			$params = array($record_id);
			$sth 	= mf_do_query($query,$params,$dbh);
			$row 	= mf_do_fetch_result($sth);

			if(!empty($row['total_row'])){
				$condition_status = true;
			}
		}else if($element_type == 'money' || $element_type == 'number'){

			if($rule_condition == 'is'){
				$where_operand = '=';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'less_than'){
				$where_operand = '<';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'greater_than'){
				$where_operand = '>';
				$where_keyword = "'{$rule_keyword}'";
			}

			//get the entered value on the table
			$query = "select 
							count(`id`) total_row 
						from 
							".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} 
					   where 
					   		`{$element_name}` {$where_operand} {$where_keyword} 
					   		and `{$record_name}` = ?";
			
			$params = array($record_id);
			$sth 	= mf_do_query($query,$params,$dbh);
			$row 	= mf_do_fetch_result($sth);

			if(!empty($row['total_row'])){
				$condition_status = true;
			}
		}else if($element_type == 'matrix_radio'){
			
			if($rule_condition == 'is'){
				$where_operand = '=';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'is_not'){
				$where_operand = '<>';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'begins_with'){
				$where_operand = 'LIKE';
				$where_keyword = "'{$rule_keyword}%'";
			}else if($rule_condition == 'ends_with'){
				$where_operand = 'LIKE';
				$where_keyword = "'%{$rule_keyword}'";
			}else if($rule_condition == 'contains'){
				$where_operand = 'LIKE';
				$where_keyword = "'%{$rule_keyword}%'";
			}else if($rule_condition == 'not_contain'){
				$where_operand = 'NOT LIKE';
				$where_keyword = "'%{$rule_keyword}%'";
			}

			//get the entered value on the table
			$query = "SELECT 
							count(B.element_title) total_row 
					    FROM(
							 SELECT 
								   A.`{$element_name}`,
								   (select 
								   		  `option` 
								   	  from 
								   	  	  ".MF_TABLE_PREFIX."element_options 
								   	 where 
								   	 	  form_id = ? and 
								   	 	  element_id = ? and 
								   	 	  option_id = A.element_{$element_id} and 
								   	 	  live = 1) element_title
							   FROM 
							  	   ".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} A
							  WHERE 
							  	   `{$record_name}` = ?
							) B 
					   WHERE 
					   		B.element_title {$where_operand} {$where_keyword}";
			
			if(!empty($element_matrix_parent_id)){
				$element_id = $element_matrix_parent_id;
			}
			
			$params = array($form_id,$element_id,$record_id);
			$sth 	= mf_do_query($query,$params,$dbh);
			$row 	= mf_do_fetch_result($sth);

			if(!empty($row['total_row'])){
				$condition_status = true;
			}

		}else if($element_type == 'matrix_checkbox' || $element_type == 'checkbox'){
			
			if($rule_condition == 'is_one'){
				$where_operand = '>';
				$where_keyword = "'0'";
			}else if($rule_condition == 'is_zero'){
				$where_operand = '=';
				$where_keyword = "'0'";
			}

			//get the entered value on the table
			$query = "select 
							count(`id`) total_row 
						from 
							".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} 
					   where 
					   		`{$element_name}` {$where_operand} {$where_keyword} 
					   		and `{$record_name}` = ?";
			
			$params = array($record_id);
			$sth 	= mf_do_query($query,$params,$dbh);
			$row 	= mf_do_fetch_result($sth);

			if(!empty($row['total_row'])){
				$condition_status = true;
			}
			
		}else if($element_type == 'date' || $element_type == 'europe_date'){

			$exploded = array();
			$exploded = explode('/', $rule_keyword); //rule keyword format -> mm/dd/yyyy

			$rule_keyword = $exploded[2].'-'.$exploded[0].'-'.$exploded[1]; //this should be yyyy-mm-dd

			if($rule_condition == 'is'){
				$where_operand = '=';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'is_before'){
				$where_operand = '<';
				$where_keyword = "'{$rule_keyword}'";
			}else if($rule_condition == 'is_after'){
				$where_operand = '>';
				$where_keyword = "'{$rule_keyword}'";
			}

			//get the entered value on the table
			$query = "select 
							count(`id`) total_row 
						from 
							".MF_TABLE_PREFIX."form_{$form_id}{$table_suffix} 
					   where 
					   		date(`{$element_name}`) {$where_operand} {$where_keyword} 
					   		and `{$record_name}` = ?";
			
			$params = array($record_id);
			$sth 	= mf_do_query($query,$params,$dbh);
			$row 	= mf_do_fetch_result($sth);

			if(!empty($row['total_row'])){
				$condition_status = true;
			}
		}

    	return $condition_status;
    }

    //this function is similar as mf_get_condition_status_from_table()
    //the only difference is the input coming from the user input, not from the table
    function mf_get_condition_status_from_input($dbh,$condition_params,$user_input){
    	
    	$form_id 		= (int) $condition_params['form_id'];
		$element_name 	= $condition_params['element_name']; //this could be 'element_x' or 'element_x_x'
		$rule_condition = $condition_params['rule_condition'];
		$rule_keyword 	= strtolower($condition_params['rule_keyword']); //keyword is case insensitive

		$condition_status = false; //the default status if false

		$exploded = explode('_', $element_name);
		$element_id = (int) $exploded[1];

		//get the element properties of the current element id
		$query 	= "select 
						 element_type,
						 element_choice_has_other,
						 element_time_showsecond,
						 element_time_24hour,
						 element_constraint,
						 element_matrix_parent_id,
						 element_matrix_allow_multiselect
					 from 
					 	 ".MF_TABLE_PREFIX."form_elements 
					where 
						 form_id = ? and element_id = ?";
		$params = array($form_id,$element_id);
		$sth 	= mf_do_query($query,$params,$dbh);
		$row 	= mf_do_fetch_result($sth);

		$element_type 			  = $row['element_type'];
		$element_choice_has_other = $row['element_choice_has_other'];
		$element_time_showsecond  = (int) $row['element_time_showsecond'];
		$element_time_24hour	  = (int) $row['element_time_24hour'];
		$element_constraint		  = $row['element_constraint'];
		$element_matrix_parent_id = (int) $row['element_matrix_parent_id'];
		$element_matrix_allow_multiselect = (int) $row['element_matrix_allow_multiselect'];

		//if this is matrix field, we need to determine wether this is matrix choice or matrix checkboxes
		if($element_type == 'matrix'){
			if(empty($element_matrix_parent_id)){
				if(!empty($element_matrix_allow_multiselect)){
					$element_type = 'matrix_checkbox';
				}else{
					$element_type = 'matrix_radio';
				}
			}else{
				//this is a child row of a matrix, get the parent id first and check the status of the multiselect option
				$query = "select element_matrix_allow_multiselect from ".MF_TABLE_PREFIX."form_elements where form_id = ? and element_id = ?";
				$params = array($form_id,$element_matrix_parent_id);
				$sth 	= mf_do_query($query,$params,$dbh);
				$row 	= mf_do_fetch_result($sth);

				if(!empty($row['element_matrix_allow_multiselect'])){
					$element_type = 'matrix_checkbox';
				}else{
					$element_type = 'matrix_radio';
				}
			}
		}

		if(in_array($element_type, array('text','textarea','simple_name','name','simple_name_wmiddle','name_wmiddle','address','phone','simple_phone','email','url'))){
			
			if($element_type == 'phone'){
				$element_value = $user_input[$element_name.'_1'].$user_input[$element_name.'_2'].$user_input[$element_name.'_3'];
			}else{
				$element_value = strtolower($user_input[$element_name]);
			}

			if($rule_condition == 'is'){
				if($element_value == $rule_keyword){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'is_not'){
				if($element_value != $rule_keyword){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'begins_with'){
				if(stripos($element_value,$rule_keyword) === 0){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'ends_with'){
				if(!empty($element_value) && substr_compare($element_value, $rule_keyword, strlen($element_value)-strlen($rule_keyword), strlen($rule_keyword), true) === 0){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'contains'){
				if(stripos($element_value,$rule_keyword) !== false){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'not_contain'){
				if(stripos($element_value,$rule_keyword) === false){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}
		}else if($element_type == 'radio' || $element_type == 'select'){
			
			$query = "select 
							`option` 
						from 
							".MF_TABLE_PREFIX."element_options 
					   where 
					   		form_id = ? and element_id = ? and live = 1 and option_id = ?";
			
			$params = array($form_id,$element_id,$user_input[$element_name]);
			
			$sth 	= mf_do_query($query,$params,$dbh);
			$row 	= mf_do_fetch_result($sth);

			if(!empty($row['option']) || $row['option'] == 0 || $row['option'] == '0'){
				$element_value = strtolower($row['option']);
			}else{

				//if the choice has 'other' and the user entered the value
				if(!empty($element_choice_has_other) && !empty($user_input[$element_name.'_other'])){
					$query = "select element_choice_other_label from ".MF_TABLE_PREFIX."form_elements where form_id = ? and element_id = ?";
					$params = array($form_id,$element_id);
					$sth 	= mf_do_query($query,$params,$dbh);
					$row 	= mf_do_fetch_result($sth);

					$element_value = strtolower($row['element_choice_other_label']);
				}
			}
			
			if($rule_condition == 'is'){
				if($element_value == $rule_keyword){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'is_not'){
				if($element_value != $rule_keyword){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'begins_with'){
				if(stripos($element_value,$rule_keyword) === 0){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'ends_with'){
				if(!empty($element_value) && substr_compare($element_value, $rule_keyword, strlen($element_value)-strlen($rule_keyword), strlen($rule_keyword), true) === 0){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'contains'){
				if(stripos($element_value,$rule_keyword) !== false){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'not_contain'){
				if(stripos($element_value,$rule_keyword) === false){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}
		}else if($element_type == 'time'){
			//there are few variants of the the time field, get the specific type
			if(!empty($element_time_showsecond) && !empty($element_time_24hour)){
				$element_type = 'time_showsecond24hour';
			}else if(!empty($element_time_showsecond) && empty($element_time_24hour)){
				$element_type = 'time_showsecond';
			}else if(empty($element_time_showsecond) && !empty($element_time_24hour)){
				$element_type = 'time_24hour';
			}

			$exploded = array();
			$exploded = explode(':', $rule_keyword); //rule keyword format -> HH:MM:SS:AM

			if($element_type == 'time'){
				$rule_keyword  = "{$exploded[0]}:{$exploded[1]}:00 {$exploded[3]}";
				$element_value = $user_input[$element_name."_1"].":".$user_input[$element_name."_2"].":00 ".$user_input[$element_name."_4"];
			}else if($element_type == 'time_showsecond'){
				$rule_keyword  = "{$exploded[0]}:{$exploded[1]}:{$exploded[2]} {$exploded[3]}";
				$element_value = $user_input[$element_name."_1"].":".$user_input[$element_name."_2"].":".$user_input[$element_name."_3"]." ".$user_input[$element_name."_4"];
			}else if($element_type == 'time_24hour'){
				$rule_keyword  = "{$exploded[0]}:{$exploded[1]}:00";
				$element_value = $user_input[$element_name."_1"].":".$user_input[$element_name."_2"].":00";
			}else if($element_type == 'time_showsecond24hour'){
				$rule_keyword  = "{$exploded[0]}:{$exploded[1]}:{$exploded[2]}";
				$element_value = $user_input[$element_name."_1"].":".$user_input[$element_name."_2"].":".$user_input[$element_name."_3"];
			}

			$rule_keyword  = strtotime($rule_keyword);
			$element_value = strtotime($element_value);

			if($element_value !== false){
				if($rule_condition == 'is'){
					if($element_value == $rule_keyword){
						$condition_status = true;
					}else{
						$condition_status = false;
					}
				}else if($rule_condition == 'is_before'){
					if($element_value < $rule_keyword){
						$condition_status = true;
					}else{
						$condition_status = false;
					}
				}else if($rule_condition == 'is_after'){
					if($element_value > $rule_keyword){
						$condition_status = true;
					}else{
						$condition_status = false;
					}
				}
			}

		}else if($element_type == 'money' || $element_type == 'number'){

			if($element_type == 'money'){
				if($element_constraint == 'yen'){ //yen only have one field
					$element_value = (float) $user_input[$element_name];
				}else{
					$element_value = (float) $user_input[$element_name."_1"].".".$user_input[$element_name."_2"];
				}
			}else if($element_type == 'number'){
				$element_value = (float) $user_input[$element_name];
			}

			$rule_keyword = (float) $rule_keyword;

			if($rule_condition == 'is'){
				if($element_value == $rule_keyword){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'less_than'){
				if($element_value < $rule_keyword){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'greater_than'){
				if($element_value > $rule_keyword){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}

		}else if($element_type == 'matrix_radio'){
			$query = "select 
							`option` 
						from 
							".MF_TABLE_PREFIX."element_options 
					   where 
							form_id = ? and 
							element_id = ? and 
							option_id = ?";
			
			if(!empty($element_matrix_parent_id)){
				$element_id = $element_matrix_parent_id;
			}

			$params = array($form_id,$element_id,$user_input[$element_name]);
			$sth 	= mf_do_query($query,$params,$dbh);
			$row 	= mf_do_fetch_result($sth);
			$element_value = strtolower($row['option']);

			if($rule_condition == 'is'){
				if($element_value == $rule_keyword){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'is_not'){
				if($element_value != $rule_keyword){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'begins_with'){
				if(stripos($element_value,$rule_keyword) === 0){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'ends_with'){
				if(!empty($element_value) && substr_compare($element_value, $rule_keyword, strlen($element_value)-strlen($rule_keyword), strlen($rule_keyword), true) === 0){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'contains'){
				if(stripos($element_value,$rule_keyword) !== false){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'not_contain'){
				if(stripos($element_value,$rule_keyword) === false){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}
		}else if($element_type == 'matrix_checkbox' || $element_type == 'checkbox'){
			$element_value = $user_input[$element_name];
			
			if($rule_condition == 'is_one'){
				if(!empty($element_value)){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}else if($rule_condition == 'is_zero'){
				if(empty($element_value)){
					$condition_status = true;
				}else{
					$condition_status = false;
				}
			}
		}else if($element_type == 'date' || $element_type == 'europe_date'){
			$exploded = array();
			$exploded = explode('/', $rule_keyword); //rule keyword format -> mm/dd/yyyy

			$rule_keyword = strtotime($exploded[2].'-'.$exploded[0].'-'.$exploded[1]); //this should be yyyy-mm-dd
			
			if($element_type == 'date'){
				$element_value = $user_input[$element_name."_3"]."-".$user_input[$element_name."_1"]."-".$user_input[$element_name."_2"];
			}else if($element_type == 'europe_date'){
				$element_value = $user_input[$element_name."_3"]."-".$user_input[$element_name."_2"]."-".$user_input[$element_name."_1"];
			}
			$element_value = strtotime($element_value);

			if($element_value !== false){
				if($rule_condition == 'is'){
					if($element_value == $rule_keyword){
						$condition_status = true;
					}else{
						$condition_status = false;
					}
				}else if($rule_condition == 'is_before'){
					if($element_value < $rule_keyword){
						$condition_status = true;
					}else{
						$condition_status = false;
					}
				}else if($rule_condition == 'is_after'){
					if($element_value > $rule_keyword){
						$condition_status = true;
					}else{
						$condition_status = false;
					}
				}
			}
		}

		return $condition_status;
    }

    //this function simplify getting data from ap_forms table
    function mf_get_form_properties($dbh,$form_id,$columns=array()){
    	
    	if(!empty($columns)){
    		$columns_joined = implode("`,`",$columns);
    	}else{
    		//if no columns array specified, get all columns of ap_forms table
    		$query = "show columns from ".MF_TABLE_PREFIX."forms";
			$params = array();
			
			$sth = mf_do_query($query,$params,$dbh);
			while($row = mf_do_fetch_result($sth)){
				if($row['Field'] == 'form_id' || $row['Field'] == 'form_name'){
					continue; //MySQL 4.1 doesn't support WHERE on show columns, hence we need this
				}
				$columns[] = $row['Field'];
			}
			
			$columns_joined = implode("`,`",$columns);
    	}
    	
    	$query = "select `{$columns_joined}` from ".MF_TABLE_PREFIX."forms where form_id = ?";
    	$params = array($form_id);

    	$sth = mf_do_query($query,$params,$dbh);
    	$row = mf_do_fetch_result($sth);

    	$form_properties = array();
		foreach ($columns as $column_name) {
			$form_properties[$column_name] = $row[$column_name];
		}

		return $form_properties;
    }

    //this function returns all template variables and values of an entry within a form
    function mf_get_template_variables($dbh,$form_id,$entry_id,$options=array()){
    	
    	global $mf_lang;
    	
    	$entry_options = array();

    	$as_plain_text   = $options['as_plain_text'];
    	$target_is_admin = $options['target_is_admin'];    
	    
	    $entry_options['strip_download_link'] 	= $options['strip_download_link'];
	    $entry_options['strip_checkbox_image'] 	= true;
	    $entry_options['machform_path'] 		= $options['machform_path']; //the path to machform
			
		//get data for the particular entry id
		$entry_details = mf_get_entry_details($dbh,$form_id,$entry_id,$entry_options);

		//if the form has payment enabled, get the payment details
		//start getting payment details -----------------------
		$query 	= "select 
					 payment_enable_merchant,
					 payment_merchant_type,
					 payment_price_type,
					 payment_price_amount,
					 payment_currency,
					 payment_ask_billing,
					 payment_ask_shipping,
					 payment_enable_tax,
					 payment_tax_rate,
					 payment_enable_discount,
					 payment_discount_type,
					 payment_discount_amount,
					 payment_discount_element_id,
					 form_page_total,
					 logic_field_enable 
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		if(!empty($row)){
			$payment_enable_merchant = (int) $row['payment_enable_merchant'];
			if($payment_enable_merchant < 1){
				$payment_enable_merchant = 0;
			}
			
			$payment_price_amount 	= (double) $row['payment_price_amount'];
			$payment_merchant_type 	= $row['payment_merchant_type'];
			$payment_price_type 	= $row['payment_price_type'];
			$form_payment_currency 	= strtoupper($row['payment_currency']);
			$payment_ask_billing 	= (int) $row['payment_ask_billing'];
			$payment_ask_shipping 	= (int) $row['payment_ask_shipping'];

			$payment_enable_tax = (int) $row['payment_enable_tax'];
			$payment_tax_rate 	= (float) $row['payment_tax_rate'];

			$payment_enable_discount = (int) $row['payment_enable_discount'];
			$payment_discount_type 	 = $row['payment_discount_type'];
			$payment_discount_amount = (float) $row['payment_discount_amount'];
			$payment_discount_element_id = (int) $row['payment_discount_element_id'];

			$logic_field_enable 	= (int) $row['logic_field_enable'];
			$form_page_total 		= $row['form_page_total'];

		}

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

		if(!empty($payment_enable_merchant)){
			$query = "SELECT 
							`payment_id`,
							 date_format(payment_date,'%e %b %Y - %r') payment_date, 
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
							`shipping_country`
						FROM
							".MF_TABLE_PREFIX."form_payments
					   WHERE
					   		form_id = ? and record_id = ? and `status` = 1
					ORDER BY
							payment_date DESC
					   LIMIT 1";
			
			$params = array($form_id,$entry_id);
			
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			$payment_id 		= $row['payment_id'];
			$payment_date 		= $row['payment_date'];
			$payment_status 	= $row['payment_status'];
			$payment_fullname 	= $row['payment_fullname'];
			$payment_amount 	= (double) $row['payment_amount'];
			$payment_currency 	= strtoupper($row['payment_currency']);
			$payment_test_mode 	= (int) $row['payment_test_mode'];
			$payment_merchant_type = $row['payment_merchant_type'];
			$billing_street 	= htmlspecialchars(trim($row['billing_street']));
			$billing_city 		= htmlspecialchars(trim($row['billing_city']));
			$billing_state 		= htmlspecialchars(trim($row['billing_state']));
			$billing_zipcode 	= htmlspecialchars(trim($row['billing_zipcode']));
			$billing_country 	= htmlspecialchars(trim($row['billing_country']));
			
			$same_shipping_address = (int) $row['same_shipping_address'];

			if(!empty($same_shipping_address)){
				$shipping_street 	= $billing_street;
				$shipping_city		= $billing_city;
				$shipping_state		= $billing_state;
				$shipping_zipcode	= $billing_zipcode;
				$shipping_country	= $billing_country;
			}else{
				$shipping_street 	= htmlspecialchars(trim($row['shipping_street']));
				$shipping_city 		= htmlspecialchars(trim($row['shipping_city']));
				$shipping_state 	= htmlspecialchars(trim($row['shipping_state']));
				$shipping_zipcode 	= htmlspecialchars(trim($row['shipping_zipcode']));
				$shipping_country 	= htmlspecialchars(trim($row['shipping_country']));
			}

			if(!empty($billing_street) || !empty($billing_city) || !empty($billing_state) || !empty($billing_zipcode) || !empty($billing_country)){
				$billing_address  = "{$billing_street}<br />{$billing_city}, {$billing_state} {$billing_zipcode}<br />{$billing_country}";
			}
			
			if(!empty($shipping_street) || !empty($shipping_city) || !empty($shipping_state) || !empty($shipping_zipcode) || !empty($shipping_country)){
				$shipping_address = "{$shipping_street}<br />{$shipping_city}, {$shipping_state} {$shipping_zipcode}<br />{$shipping_country}";
			}

			if(!empty($row)){
				$payment_has_record = true;
			}else{
				//if the entry doesn't have any record within ap_form_payments table
				//we need to calculate the total amount
				$payment_has_record = false;
				$payment_status = "unpaid";
				
				if($payment_price_type == 'variable'){
					$payment_amount = (double) mf_get_payment_total($dbh,$form_id,$entry_id,0,'live');
				}else if($payment_price_type == 'fixed'){
					$payment_amount = $payment_price_amount;
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

				$payment_currency = $form_payment_currency;
			}

			if(!$as_plain_text){
				switch ($payment_currency) {
					case 'USD' : $currency_symbol = '&#36;';break;
					case 'EUR' : $currency_symbol = '&#8364;';break;
					case 'GBP' : $currency_symbol = '&#163;';break;
					case 'AUD' : $currency_symbol = '&#36;';break;
					case 'CAD' : $currency_symbol = '&#36;';break;
					case 'JPY' : $currency_symbol = '&#165;';break;
					case 'THB' : $currency_symbol = '&#3647;';break;
					case 'HUF' : $currency_symbol = '&#70;&#116;';break;
					case 'CHF' : $currency_symbol = 'CHF';break;
					case 'CZK' : $currency_symbol = '&#75;&#269;';break;
					case 'SEK' : $currency_symbol = 'kr';break;
					case 'DKK' : $currency_symbol = 'kr';break;
					case 'NOK' : $currency_symbol = 'kr';break;
					case 'PHP' : $currency_symbol = '&#36;';break;
					case 'IDR' : $currency_symbol = 'Rp';break;
					case 'MYR' : $currency_symbol = 'RM';break;
					case 'PLN' : $currency_symbol = '&#122;&#322;';break;
					case 'BRL' : $currency_symbol = 'R&#36;';break;
					case 'HKD' : $currency_symbol = '&#36;';break;
					case 'MXN' : $currency_symbol = 'Mex&#36;';break;
					case 'TWD' : $currency_symbol = 'NT&#36;';break;
					case 'TRY' : $currency_symbol = 'TL';break;
					case 'NZD' : $currency_symbol = '&#36;';break;
					case 'SGD' : $currency_symbol = '&#36;';break;
					default: $currency_symbol = ''; break;
				}
			}else{
				switch ($payment_currency) {
					case 'USD' : $currency_symbol = '$';break;
					case 'EUR' : $currency_symbol = '€';break;
					case 'GBP' : $currency_symbol = '£';break;
					case 'AUD' : $currency_symbol = '$';break;
					case 'CAD' : $currency_symbol = '$';break;
					case 'JPY' : $currency_symbol = '¥';break;
					case 'THB' : $currency_symbol = '฿';break;
					case 'HUF' : $currency_symbol = 'Ft';break;
					case 'CHF' : $currency_symbol = 'CHF';break;
					case 'CZK' : $currency_symbol = 'Kč';break;
					case 'SEK' : $currency_symbol = 'kr';break;
					case 'DKK' : $currency_symbol = 'kr';break;
					case 'NOK' : $currency_symbol = 'kr';break;
					case 'PHP' : $currency_symbol = '$';break;
					case 'IDR' : $currency_symbol = 'Rp';break;
					case 'MYR' : $currency_symbol = 'RM';break;
					case 'PLN' : $currency_symbol = 'zł';break;
					case 'BRL' : $currency_symbol = 'R$';break;
					case 'HKD' : $currency_symbol = '$';break;
					case 'MXN' : $currency_symbol = '$';break;
					case 'TWD' : $currency_symbol = '$';break;
					case 'TRY' : $currency_symbol = 'TL';break;
					case 'NZD' : $currency_symbol = '$';break;
					case 'SGD' : $currency_symbol = '$';break;
					default: $currency_symbol = ''; break;
				}
			}

			$total_payment_amount = $currency_symbol.$payment_amount.' '.$payment_currency;

			$total_entry_details = count($entry_details);
			
			//blank row for separator
			if(!$as_plain_text){
				$entry_details[$total_entry_details]['value'] = '&nbsp;&nbsp;';
				$entry_details[$total_entry_details]['label'] = '&nbsp;&nbsp;';
			}else{
				$entry_details[$total_entry_details]['value'] = '';
				$entry_details[$total_entry_details]['label'] = '';
			}
			
			//get total amount
			$total_entry_details++;
			$entry_details[$total_entry_details]['value'] = $total_payment_amount;
			$entry_details[$total_entry_details]['label'] = $mf_lang['payment_total'];

			//get payment status
			//don't include 'unpaid' payment status within the email, to avoid confusion
			if($payment_status != 'unpaid'){
				$total_entry_details++;
				if(!empty($payment_test_mode)){
					$entry_details[$total_entry_details]['value'] = strtoupper($payment_status).' (TEST mode)';
				}else{
					$entry_details[$total_entry_details]['value'] = strtoupper($payment_status);
				}			
				$entry_details[$total_entry_details]['label'] = $mf_lang['payment_status'];
			}

			
			if($payment_has_record){

				//get payment id
				$total_entry_details++;
				$entry_details[$total_entry_details]['value'] = $payment_id;
				$entry_details[$total_entry_details]['label'] = $mf_lang['payment_id'];

				//get payment date
				$total_entry_details++;
				$entry_details[$total_entry_details]['value'] = $payment_date;
				$entry_details[$total_entry_details]['label'] = $mf_lang['payment_date'];

				//blank row for separator
				$total_entry_details++;
				if(!$as_plain_text){
					$entry_details[$total_entry_details]['value'] = '&nbsp;&nbsp;';
					$entry_details[$total_entry_details]['label'] = '&nbsp;&nbsp;';
				}else{
					$entry_details[$total_entry_details]['value'] = '';
					$entry_details[$total_entry_details]['label'] = '';
				}

				//get full name
				$total_entry_details++;
				$entry_details[$total_entry_details]['value'] = htmlspecialchars($payment_fullname,ENT_QUOTES);
				$entry_details[$total_entry_details]['label'] = $mf_lang['payment_fullname'];

				//get billing address
				if(!empty($payment_ask_billing) && !empty($billing_address)){
					$total_entry_details++;
					$entry_details[$total_entry_details]['value'] = $billing_address;
					$entry_details[$total_entry_details]['label'] = $mf_lang['payment_billing'];
				}

				//get shipping address
				if(!empty($payment_ask_shipping) && !empty($shipping_address)){
					$total_entry_details++;
					$entry_details[$total_entry_details]['value'] = $shipping_address;
					$entry_details[$total_entry_details]['label'] = $mf_lang['payment_shipping'];
				}
			}


		} //end payment enable merchant
		//end getting payment details -----------------------

		//populate field values to template variables
    	$i=0;
    	foreach ($entry_details as $data){
    		if(empty($data['element_id'])){
    			continue;
    		}

    		if($as_plain_text){
    			$data['value'] = htmlspecialchars_decode($data['value'],ENT_QUOTES);
    			$data['value'] = str_replace('<br />', "\n", $data['value']);
    			
    			if($data['element_type'] != 'file'){
    				$data['value'] = str_replace('&nbsp;', "", $data['value']);
    			}
    			
    			$data['value'] = strip_tags($data['value']);
    		}

    		$template_variables[$i] = '{element_'.$data['element_id'].'}';
    		$template_values[$i]	= $data['value'];
    		
    		if($data['element_type'] == 'textarea' && !$as_plain_text){
				$template_values[$i] = nl2br($data['value']);
			}elseif ($data['element_type'] == 'file'){
				if(!$as_plain_text){
					$template_values[$i] = strip_tags($data['value'],'<a><br/><br>');
				}else{
					$template_values[$i] = strip_tags($data['value']);
					$template_values[$i] = str_replace('&nbsp;', "\n- ", $template_values[$i]);
				}
			}elseif ($data['element_type'] == 'signature'){
				//skip the signature, we will construct the value later below
				continue;
			}else{
				$template_values[$i] = $data['value'];
			}
    		    		
    		$i++;
    	}
    	
    	$entry_values = mf_get_entry_values($dbh,$form_id,$entry_id);

    	//get template variables for some complex fields (name and address)
		$query  = "select 
						 element_id,
						 element_type 
				     from
				     	 `".MF_TABLE_PREFIX."form_elements` 
				    where 
				    	 form_id=? and 
				    	 element_type != 'section' and 
				    	 element_status=1 and
				    	 element_type in('simple_name','simple_name_wmiddle','name','name_wmiddle','address')
				 order by 
				 		 element_position asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);	
	
		while($row = mf_do_fetch_result($sth)){
			$element_id    = $row['element_id'];
			$element_type  = $row['element_type']; 
			
			if('simple_name' == $element_type){
				$total_sub_field = 2;
			}else if('simple_name_wmiddle' == $element_type){
				$total_sub_field = 3;	
			}else if('name' == $element_type){
				$total_sub_field = 4;
			}else if('name_wmiddle' == $element_type){
				$total_sub_field = 5;
			}else if('address' == $element_type){
				$total_sub_field = 6;
			}

			for($j=1;$j<=$total_sub_field;$j++){
				$template_variables[$i] = '{element_'.$element_id.'_'.$j.'}';
    			$template_values[$i]	= $entry_values['element_'.$element_id.'_'.$j]['default_value'];
    			$i++;
			}
		}
		
    	//get entry timestamp
		$query = "select date_created,ip_address from `".MF_TABLE_PREFIX."form_{$form_id}` where id=?";
		$params = array($entry_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$date_created = $row['date_created'];
		$ip_address   = $row['ip_address'];
    	    	
    	//get form name
		$query 	= "select form_name	from `".MF_TABLE_PREFIX."forms` where form_id=?";
		$params = array($form_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$form_name  = $row['form_name'];
    	
    	
		$template_variables[$i] = '{date_created}';
		$template_values[$i]	= $date_created;
		$i++;
		$template_variables[$i] = '{ip_address}';
		$template_values[$i]	= $ip_address;
		$i++;
		$template_variables[$i] = '{form_name}';
		$template_values[$i]	= $form_name;
		$i++;
		$template_variables[$i] = '{entry_no}';
		$template_values[$i]	= $entry_id;
		$i++;
		$template_variables[$i] = '{form_id}';
		$template_values[$i]	= $form_id;
		
		//populate template variables for payment details
		if(!empty($total_payment_amount)){
			$i++;
			$template_variables[$i] = '{total_amount}';
			$template_values[$i]	= $total_payment_amount;
		}

		if(!empty($payment_status)){
			$i++;
			$template_variables[$i] = '{payment_status}';

			if(!empty($payment_test_mode)){
				$template_values[$i]	= strtoupper($payment_status).' (TEST mode)';
			}else{
				$template_values[$i]	= strtoupper($payment_status);
			}
		}

		if(!empty($payment_id)){
			$i++;
			$template_variables[$i] = '{payment_id}';
			$template_values[$i]	= $payment_id;
		}

		if(!empty($payment_date)){
			$i++;
			$template_variables[$i] = '{payment_date}';
			$template_values[$i]	= $payment_date;
		}

		if(!empty($payment_fullname)){
			$i++;
			$template_variables[$i] = '{payment_fullname}';
			$template_values[$i]	= $payment_fullname;
		}
		
		if(!empty($billing_address)){
			
			if($as_plain_text){
				$billing_address = str_replace('<br />', "\n", $billing_address);
			}

			$i++;
			$template_variables[$i] = '{billing_address}';
			$template_values[$i]	= $billing_address;
		}

		if(!empty($shipping_address)){
			
			if($as_plain_text){
				$shipping_address = str_replace('<br />', "\n", $shipping_address);
			}
			
			$i++;
			$template_variables[$i] = '{shipping_address}';
			$template_values[$i]	= $shipping_address;
		}

		
		//compose {entry_data} based on 'as_plain_text' preferences
		$email_body = '';
		$files_to_attach = array();
		
		//if logic is enable, get hidden elements
		//we'll need it to hide section break
		if($logic_field_enable){
			$entry_values = mf_get_entry_values($dbh,$form_id,$entry_id,false);
			foreach ($entry_values as $element_name => $value) {
				$input_data[$element_name] = $value['default_value'];
			}

			$hidden_elements = array();
			for($x=1;$x<=$form_page_total;$x++){
				$current_page_hidden_elements = array();
				$current_page_hidden_elements = mf_get_hidden_elements($dbh,$form_id,$x,$input_data);
				
				$hidden_elements += $current_page_hidden_elements; //use '+' so that the index won't get lost
			}
		}

    	if(!$as_plain_text){
			//compose html format
			$email_body = '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-family:Lucida Grande,Tahoma,Arial,Verdana,sans-serif;font-size:12px;text-align:left">'."\n";
			
			$toggle = false;
			$j=0;
			foreach ($entry_details as $data){
				//0 should be displayed, empty string don't
				if((empty($data['value']) || $data['value'] == '&nbsp;') && $data['value'] !== 0 && $data['value'] !== '0' && $data['element_type'] !== 'section'){
					continue;
				}				
				
				//skip pagebreak
				if($data['label'] == 'mf_page_break' && $data['value'] == 'mf_page_break'){
					continue;
				}


				if($toggle){
					$toggle = false;
					$row_style = 'style="background-color:#F3F7FB"';
				}else{
					$toggle = true;
					$row_style = '';
				}
			
				if($data['element_type'] == 'textarea'){
					$data['value'] = nl2br($data['value']);
				}elseif ($data['element_type'] == 'file'){
					
					if($target_is_admin === false){
						$data['value'] = strip_tags($data['value'],'<a><br/><br>');
						$data['value'] = str_replace('&nbsp;', '', $data['value']);
					}else{
						$data['value'] = strip_tags($data['value'],'<a><br/><br>');
						$data['value'] = str_replace('&nbsp;', '', $data['value']);
						
						//if there is file to be attached
						if(!empty($data['filedata'])){
							foreach ($data['filedata'] as $file_info){
								$files_to_attach[$j]['filename_path']  = $file_info['filename_path'];
								$files_to_attach[$j]['filename_value'] = $file_info['filename_value'];
								$j++;
							}
						}
					}
				}elseif($data['element_type'] == 'signature'){
					$element_id = $data['element_id'];
					$signature_hash = md5($data['value']);

					//encode the long query string for more readibility
					$q_string = base64_encode("form_id={$form_id}&id={$entry_id}&el=element_{$element_id}&hash={$signature_hash}");
					
					if(!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')){
						$ssl_suffix = 's';
					}else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
			            $ssl_suffix = 's';
			        }else if (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] == 'on'){
			            $ssl_suffix = 's';
			        }else{
						$ssl_suffix = '';
					}

					if(!empty($email_param['machform_base_path'])){ //if the form is called from advanced form code
						$data['value'] = '<a href="'.$email_param['machform_base_path'].'signature.php?q='.$q_string.'">View Signature</a>';
					}else{
						$data['value'] = '<a href="http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/signature.php?q='.$q_string.'">View Signature</a>';
					}

					//construct template variables
					$i++;
					$template_variables[$i] = '{element_'.$data['element_id'].'}';
    				$template_values[$i]	= $data['value'];
				}
				
				if($data['element_type'] == 'section'){
					//if this section break is hidden due to logic, don't display it
					if(!empty($hidden_elements) && !empty($hidden_elements[$data['element_id']])){
						continue;
					}

					if(!empty($data['label']) && !empty($data['value']) && ($data['value'] != '&nbsp;')){
						$section_separator = '<br/>';
					}else{
						$section_separator = '';
					}

					$section_break_content = '<span><strong>'.nl2br($data['label']).'</strong></span>'.$section_separator.'<span>'.nl2br($data['value']).'</span>';
					
					$email_body .= "<tr {$row_style}>\n";
					$email_body .= '<td width="100%" colspan="2" style="border-bottom:1px solid #DEDEDE;padding:5px 10px;">'.$section_break_content.'</td>'."\n";
					$email_body .= '</tr>'."\n";
				}else{
					$email_body .= "<tr {$row_style}>\n";
					$email_body .= '<td width="40%" style="border-bottom:1px solid #DEDEDE;padding:5px 10px;"><strong>'.$data['label'].'</strong></td>'."\n";
					$email_body .= '<td width="60%" style="border-bottom:1px solid #DEDEDE;padding:5px 10px;">'.$data['value'].'</td>'."\n";
					$email_body .= '</tr>'."\n";
				}	
					
			}
			$email_body .= "</table>\n";
		}else{
			
			$money_symbols = array('&#165;','&#163;','&#8364;','&#3647;','&#75;&#269;','&#122;&#322;','&#65020;');
			$money_plain   = array('¥','£','€','฿','Kč','zł','﷼');

			//compose text format
			foreach ($entry_details as $data){
				$data['value'] = htmlspecialchars_decode($data['value'],ENT_QUOTES);
				
				//0 should be displayed, empty string don't
				if((empty($data['value']) || $data['value'] == '&nbsp;') && $data['value'] !== 0 && $data['value'] !== '0'){
					continue;
				}
				
				//skip pagebreak
				if($data['label'] == 'mf_page_break' && $data['value'] == 'mf_page_break'){
					continue;
				}
				
				$data['value'] = str_replace('<br />', "\n", $data['value']);
								
				if($data['element_type'] == 'textarea' || $data['element_type'] == 'matrix'){
					$data['value'] = trim($data['value'],"\n");
					$email_body .= "{$data['label']}: \n".$data['value']."\n\n";
				}elseif($data['element_type'] == 'section'){
					//if this section break is hidden due to logic, don't display it
					if(!empty($hidden_elements) && !empty($hidden_elements[$data['element_id']])){
						continue;
					}
					
					$data['value'] = trim($data['value'],"\n");
					$email_body .= "{$data['label']} \n".$data['value']."\n\n";
				}elseif ($data['element_type'] == 'checkbox' || $data['element_type'] == 'address'){
					$email_body .= "{$data['label']}: \n".$data['value']."\n\n";
				}elseif ($data['element_type'] == 'file'){
					$data['value'] = strip_tags($data['value']);
					$data['value'] = str_replace('&nbsp;', "\n- ", $data['value']);
					$email_body .= "{$data['label']}: {$data['value']}\n";

					//if there is file to be attached
					if(!empty($data['filedata'])){
						foreach ($data['filedata'] as $file_info){
							$files_to_attach[$j]['filename_path']  = $file_info['filename_path'];
							$files_to_attach[$j]['filename_value'] = $file_info['filename_value'];
							$j++;
						}
					}
				}elseif($data['element_type'] == 'money'){
					$data['value'] = str_replace($money_symbols, $money_plain, $data['value']);
					$email_body .= "{$data['label']}: {$data['value']} \n\n";
				}elseif($data['element_type'] == 'url'){
					$data['value'] = strip_tags($data['value']);
					$email_body .= "{$data['label']}: {$data['value']} \n\n";
				}elseif($data['element_type'] == 'signature'){
					$element_id = $data['element_id'];
					$signature_hash = md5($data['value']);

					//encode the long query string for more readibility
					$q_string = base64_encode("form_id={$form_id}&id={$entry_id}&el=element_{$element_id}&hash={$signature_hash}");
					
					if(!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')){
						$ssl_suffix = 's';
					}else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
			            $ssl_suffix = 's';
			        }else if (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] == 'on'){
			            $ssl_suffix = 's';
			        }else{
						$ssl_suffix = '';
					}

					if(!empty($email_param['machform_base_path'])){ //if the form is called from advanced form code
						$data['value'] = $email_param['machform_base_path'].'signature.php?q='.$q_string;
					}else{
						$data['value'] = 'http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/signature.php?q='.$q_string;
					}

					$email_body .= "{$data['label']}: {$data['value']} \n\n";

					//construct template variables
					$i++;
					$template_variables[$i] = '{element_'.$data['element_id'].'}';
    				$template_values[$i]	= $data['value'];
				}else{
					$email_body .= "{$data['label']}: {$data['value']} \n\n";
				}
				
				
			}
		}
		
		$i = count($template_variables);
		$template_variables[$i] = '{entry_data}';
		$template_values[$i]	= $email_body;

		$template_data = array();
		$template_data['variables'] = $template_variables;
		$template_data['values']	= $template_values;

		return $template_data;
    }

    //parse any text for any template variabels and replace it with the the actual values
    //this function is more of a wrapper of mf_get_template_variables() function
    function mf_parse_template_variables($dbh,$form_id,$entry_id,$template_content){
    	$mf_settings = mf_get_settings($dbh);

		$template_data_options = array();
		$template_data_options['strip_download_link']  = false; 
	    $template_data_options['as_plain_text']		   = true;
	    $template_data_options['target_is_admin'] 	   = true;
		$template_data_options['machform_path'] 	   = $mf_settings['base_url'];
			
		$template_data = mf_get_template_variables($dbh,$form_id,$entry_id,$template_data_options);
			
		$template_variables = $template_data['variables'];
		$template_values    = $template_data['values'];

		//parse the form success message with the template variables
		$template_content = str_replace($template_variables,$template_values,$template_content);

		return $template_content;    	
    }

    //get 2-character ISO-3166-1 country code
  	function mf_get_country_code($country_name){
  		$country["United States"] 	= 'US';
		$country["United Kingdom"] 	= 'GB';
		$country["Canada"]	= 'CA';
		$country["Australia"]	= 'AU';
		$country["Netherlands"]	= 'NL';
		$country["France"]	= 'FR';
		$country["Germany"]	= 'DE';
		$country["Afghanistan"]	= 'AF';
		$country["Albania"]	= 'AL';
		$country["Algeria"]	= 'DZ';
		$country["Andorra"]	= 'AD';
		$country["Antigua and Barbuda"] = 'AG';
		$country["Argentina"]	= 'AR';
		$country["Armenia"]	= 'AM';
		$country["Austria"]	= 'AT';
		$country["Azerbaijan"]	= 'AZ';
		$country["Bahamas"] = 'BS';
		$country["Bahrain"] = 'BH';
		$country["Bangladesh"] = 'BD';
		$country["Barbados"] = 'BB';
		$country["Belarus"] = 'BY';
		$country["Belgium"] = 'BE';
		$country["Belize"] = 'BZ';
		$country["Benin"] = 'BJ';
		$country["Bhutan"] = 'BT';
		$country["Bolivia"] = 'BO';
		$country["Bosnia and Herzegovina"] = 'BA';
		$country["Botswana"] = 'BW';
		$country["Brazil"] = 'BR';
		$country["Brunei"] = 'BN';
		$country["Bulgaria"] = 'BG';
		$country["Burkina Faso"] = 'BF';
		$country["Burundi"] = 'BI';
		$country["Cambodia"] = 'KH';
		$country["Cameroon"] = 'CM';	
		$country["Cape Verde"] = 'CV';
		$country["Central African Republic"] = 'CF';
		$country["Chad"] = 'TD';
		$country["Chile"] = 'CL';
		$country["China"] = 'CN';
		$country["Colombia"] = 'CO';
		$country["Comoros"] = 'KM';
		$country["Congo"] = 'CG';
		$country["Costa Rica"] = 'CR';
		$country["Côte d'Ivoire"] = 'CI';
		$country["Croatia"] = 'HR';
		$country["Cuba"] = 'CU';
		$country["Cyprus"] = 'CY';
		$country["Czech Republic"] = 'CZ';
		$country["Denmark"] = 'DK';
		$country["Djibouti"] = 'DJ';
		$country["Dominica"] = 'DM';
		$country["Dominican Republic"] = 'DO';
		$country["East Timor"] = 'TL';
		$country["Ecuador"] = 'EC';
		$country["Egypt"] = 'EG';
		$country["El Salvador"] = 'SV';
		$country["Equatorial Guinea"] = 'GQ';
		$country["Eritrea"] = 'ER';
		$country["Estonia"] = 'EE';
		$country["Ethiopia"] = 'ET';
		$country["Fiji"] = 'FJ';
		$country["Finland"] = 'FI';
		$country["Gabon"] = 'GA';
		$country["Gambia"] = 'GM';
		$country["Georgia"] = 'GE';
		$country["Ghana"] = 'GH';
		$country["Greece"] = 'GR';
		$country["Grenada"] = 'GD';
		$country["Guatemala"] = 'GT';
		$country["Guernsey"] = 'GG';
		$country["Guinea"] = 'GN';
		$country["Guinea-Bissau"] = 'GW';
		$country["Guyana"] = 'GY';
		$country["Haiti"] = 'HT';
		$country["Honduras"] = 'HN';
		$country["Hong Kong"] = 'HK';
		$country["Hungary"] = 'HU';
		$country["Iceland"] = 'IS';
		$country["India"] = 'IN';
		$country["Indonesia"] = 'ID';
		$country["Iran"] = 'IR';
		$country["Iraq"] = 'IQ';
		$country["Ireland"] = 'IE';
		$country["Israel"] = 'IL';
		$country["Italy"] = 'IT';
		$country["Jamaica"] = 'JM';
		$country["Japan"] = 'JP';
		$country["Jordan"] = 'JO';
		$country["Kazakhstan"] = 'KZ';
		$country["Kenya"] = 'KE';
		$country["Kiribati"] = 'KI';
		$country["North Korea"] = 'KP';
		$country["South Korea"] = 'KR';
		$country["Kuwait"] = 'KW';
		$country["Kyrgyzstan"] = 'KG';
		$country["Laos"] = 'LA';
		$country["Latvia"] = 'LV';
		$country["Lebanon"] = 'LB';
		$country["Lesotho"] = 'LS';
		$country["Liberia"] = 'LR';
		$country["Libya"] = 'LY';
		$country["Liechtenstein"] = 'LI';
		$country["Lithuania"] = 'LT';
		$country["Luxembourg"] = 'LU';
		$country["Macedonia"] = 'MK';
		$country["Madagascar"] = 'MG';
		$country["Malawi"] = 'MW';
		$country["Malaysia"] = 'MY';
		$country["Maldives"] = 'MV';
		$country["Mali"] = 'ML';
		$country["Malta"] = 'MT';
		$country["Marshall Islands"] = 'MH';
		$country["Mauritania"] = 'MR';
		$country["Mauritius"] = 'MU';
		$country["Mexico"] = 'MX';
		$country["Micronesia"] = 'FM';
		$country["Moldova"] = 'MD';
		$country["Monaco"] = 'MC';
		$country["Mongolia"] = 'MN';
		$country["Montenegro"] = 'ME';
		$country["Morocco"] = 'MA';
		$country["Mozambique"] = 'MZ';
		$country["Myanmar"] = 'MM';
		$country["Namibia"] = 'NA';
		$country["Nauru"] = 'NR';
		$country["Nepal"] = 'NP';
		$country["New Zealand"] = 'NZ';
		$country["Nicaragua"] = 'NI';
		$country["Niger"] = 'NE';
		$country["Nigeria"] = 'NG';
		$country["Norway"] = 'NO';
		$country["Oman"] = 'OM';
		$country["Pakistan"] = 'PK';
		$country["Palau"] = 'PW';
		$country["Panama"] = 'PA';
		$country["Papua New Guinea"] = 'PG';
		$country["Paraguay"] = 'PY';
		$country["Peru"] = 'PE';
		$country["Philippines"] = 'PH';
		$country["Poland"] = 'PL';
		$country["Portugal"] = 'PT';
		$country["Puerto Rico"] = 'PR';
		$country["Qatar"] = 'QA';
		$country["Romania"] = 'RO';
		$country["Russia"] = 'RU';
		$country["Rwanda"] = 'RW';
		$country["Saint Kitts and Nevis"] = 'KN';
		$country["Saint Lucia"] = 'LC';
		$country["Saint Vincent and the Grenadines"] = 'VC';
		$country["Samoa"] = 'WS';
		$country["San Marino"] = 'SM';
		$country["Sao Tome and Principe"] = 'ST';
		$country["Saudi Arabia"] = 'SA';
		$country["Senegal"] = 'SN';
		$country["Serbia and Montenegro"] = 'RS';
		$country["Seychelles"] = 'SC';
		$country["Sierra Leone"] = 'SL';
		$country["Singapore"] = 'SG';
		$country["Slovakia"] = 'SK';
		$country["Slovenia"] = 'SI';
		$country["Solomon Islands"] = 'SB';
		$country["Somalia"] = 'SO';
		$country["South Africa"] = 'ZA';
		$country["Spain"] = 'ES';
		$country["Sri Lanka"] = 'LK';
		$country["Sudan"] = 'SD';
		$country["Suriname"] = 'SR';
		$country["Swaziland"] = 'SZ';
		$country["Sweden"] = 'SE';
		$country["Switzerland"] = 'CH';
		$country["Syria"] = 'SY';
		$country["Taiwan"] = 'TW';
		$country["Tajikistan"] = 'TJ';
		$country["Tanzania"] = 'TZ';
		$country["Thailand"] = 'TH';
		$country["Togo"] = 'TG';
		$country["Tonga"] = 'TO';
		$country["Trinidad and Tobago"] = 'TT';
		$country["Tunisia"] = 'TN';
		$country["Turkey"] = 'TR';
		$country["Turkmenistan"] = 'TM';
		$country["Tuvalu"] = 'TV';
		$country["Uganda"] = 'UG';
		$country["Ukraine"] = 'UA';
		$country["United Arab Emirates"] = 'AE';
		$country["Uruguay"] = 'UY';
		$country["Uzbekistan"] = 'UZ';
		$country["Vanuatu"] = 'VU';
		$country["Vatican City"] = 'VA';
		$country["Venezuela"] = 'VE';
		$country["Vietnam"] = 'VN';
		$country["Yemen"] = 'YE';
		$country["Zambia"] = 'ZM';
		$country["Zimbabwe"] = 'ZW';

		return $country[$country_name];
  	}

  	//trim 'text' to max_length and add '...' at the end of the text
  	function mf_trim_max_length($text,$max_length){
  		$text = (strlen($text) > ($max_length + 3)) ? substr($text,0,$max_length).'...' : $text;
  		return $text;
  	}

  	//insert into the middle of array and maintain the index of the inserted array
  	function mf_array_insert (&$array, $position, $insert_array) { 
	  	$first_array = array_splice ($array, 0, $position); 
	  	$array = array_merge ($first_array, $insert_array, $array); 
	} 
?>
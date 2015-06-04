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

	require('includes/language.php');
	require('includes/entry-functions.php');
	require('includes/post-functions.php');
	require('includes/users-functions.php');
	
	$form_id  = (int) trim($_GET['form_id']);
	$entry_id = (int) trim($_GET['entry_id']);
	$nav = trim($_GET['nav']);

	if(empty($form_id) || empty($entry_id)){
		die("Invalid Request");
	}

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);

	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_entries or view_entries permission
		if(empty($user_perms['edit_entries']) && empty($user_perms['view_entries'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

			$ssl_suffix = mf_get_ssl_suffix();						
			header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}

	//get entry information (date created/updated/ip address/resume key)
	$query = "select 
					date_format(date_created,'%e %b %Y - %r') date_created,
					date_format(date_updated,'%e %b %Y - %r') date_updated,
					ip_address,
					resume_key,
					`status` 
				from 
					`".MF_TABLE_PREFIX."form_{$form_id}` 
			where id=?";
	$params = array($entry_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$date_created = $row['date_created'];
	if(!empty($row['date_updated'])){
		$date_updated = $row['date_updated'];
	}else{
		$date_updated = '&nbsp;';
	}
	$ip_address   = $row['ip_address'];
	$entry_status = $row['status'];
	$form_resume_key = $row['resume_key'];

	$is_incomplete_entry = false;
	if($entry_status == 2){
		$is_incomplete_entry = true;
	}

	if($is_incomplete_entry && !empty($form_resume_key)){
		$form_resume_url = $mf_settings['base_url']."view.php?id={$form_id}&mf_resume={$form_resume_key}";
	}

	//if there is "nav" parameter, we need to determine the correct entry id and override the existing entry_id
	if(!empty($nav)){

		$entries_options = array();
		$entries_options['is_incomplete_entry'] = $is_incomplete_entry;
		
		$all_entry_id_array = mf_get_filtered_entries_ids($dbh,$form_id,$entries_options);
		$entry_key = array_keys($all_entry_id_array,$entry_id);
		$entry_key = $entry_key[0];

		if($nav == 'prev'){
			$entry_key--;
		}else{
			$entry_key++;
		}

		$entry_id = $all_entry_id_array[$entry_key];

		//if there is no entry_id, fetch the first/last member of the array
		if(empty($entry_id)){
			if($nav == 'prev'){
				$entry_id = array_pop($all_entry_id_array);
			}else{
				$entry_id = $all_entry_id_array[0];
			}
		}
	}
	
	//get form name
	$query 	= "select 
					 form_name,
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
					 payment_discount_element_id  
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] = mf_trim_max_length($row['form_name'],65);
		
		$form_name = htmlspecialchars($row['form_name']);
		$payment_enable_merchant = (int) $row['payment_enable_merchant'];
		if($payment_enable_merchant < 1){
			$payment_enable_merchant = 0;
		}
		
		$payment_price_amount = (double) $row['payment_price_amount'];
		$payment_merchant_type = $row['payment_merchant_type'];
		$payment_price_type = $row['payment_price_type'];
		$form_payment_currency = strtoupper($row['payment_currency']);
		$payment_ask_billing = (int) $row['payment_ask_billing'];
		$payment_ask_shipping = (int) $row['payment_ask_shipping'];

		$payment_enable_tax = (int) $row['payment_enable_tax'];
		$payment_tax_rate 	= (float) $row['payment_tax_rate'];

		$payment_enable_discount = (int) $row['payment_enable_discount'];
		$payment_discount_type 	 = $row['payment_discount_type'];
		$payment_discount_amount = (float) $row['payment_discount_amount'];
		$payment_discount_element_id = (int) $row['payment_discount_element_id'];
	}else{
		die("Error. Unknown form ID.");
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

	//if payment enabled, get the details
	if(!empty($payment_enable_merchant)){
		$query = "SELECT 
						`payment_id`,
						 date_format(payment_date,'%e %b %Y - %r') payment_date, 
						`payment_status`, 
						`payment_fullname`, 
						`payment_amount`, 
						`payment_currency`, 
						`payment_test_mode`,
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

			if(empty($payment_id)){
				//if the payment has record but has no payment id, then the record was being inserted manually (the payment status was being set manually by user)
				//in this case, we consider this record empty
				$payment_has_record = false;
			}
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

		//build payment resume URL if the status is unpaid
		//at this moment only PayPal Standard supported

		if($payment_status == 'unpaid' && $payment_merchant_type == 'paypal_standard'){
			$payment_resume_url = mf_get_merchant_redirect_url($dbh,$form_id,$entry_id);
		}

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
	}

		
	//get entry details for particular entry_id
	$param['checkbox_image'] = 'images/icons/59_blue_16.png';
	$entry_details = mf_get_entry_details($dbh,$form_id,$entry_id,$param);


	//check for any 'signature' field, if there is any, we need to include the javascript library to display the signature
	$query = "select 
					count(form_id) total_signature_field 
				from 
					".MF_TABLE_PREFIX."form_elements 
			   where 
			   		element_type = 'signature' and 
			   		element_status=1 and 
			   		form_id=?";
	$params = array($form_id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	if(!empty($row['total_signature_field'])){
		$disable_jquery_loading = true;
		$signature_pad_init = '<script type="text/javascript" src="js/jquery.min.js"></script>'."\n".
							  '<!--[if lt IE 9]><script src="js/signaturepad/flashcanvas.js"></script><![endif]-->'."\n".
							  '<script type="text/javascript" src="js/signaturepad/jquery.signaturepad.min.js"></script>'."\n".
							  '<script type="text/javascript" src="js/signaturepad/json2.min.js"></script>'."\n";
	}

	$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="css/entry_print.css" media="print">
{$signature_pad_init}
EOT;

	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post view_entry">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<?php if($is_incomplete_entry){ ?>
								<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <img src="images/icons/resultset_next.gif" /> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_entries.php?id={$form_id}'>Entries</a>"; ?> <img id="ve_a_next" src="images/icons/resultset_next.gif" /> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_incomplete_entries.php?id={$form_id}'>Incomplete</a>"; ?> <img id="ve_a_next" src="images/icons/resultset_next.gif" /> #<?php echo $entry_id; ?></h2>
								<p>Displaying incomplete entry #<?php echo $entry_id; ?></p>
							<?php }else{ ?>
								<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <img src="images/icons/resultset_next.gif" /> <?php echo "<a id=\"ve_a_entries\" class=\"breadcrumb\" href='manage_entries.php?id={$form_id}'>Entries</a>"; ?> <img id="ve_a_next" src="images/icons/resultset_next.gif" /> #<?php echo $entry_id; ?></h2>
								<p>Displaying entry #<?php echo $entry_id; ?></p>
							<?php } ?>

						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
					<div id="ve_details" data-formid="<?php echo $form_id; ?>" data-entryid="<?php echo $entry_id; ?>" data-incomplete="<?php if($is_incomplete_entry){ echo '1';}else{ echo '0';} ?>">
						<table id="ve_detail_table" width="100%" border="0" cellspacing="0" cellpadding="0">
						  <tbody>

							<?php 
									$toggle = false;
									
									foreach ($entry_details as $data){ 
										if($data['label'] == 'mf_page_break' && $data['value'] == 'mf_page_break'){
											continue;
										}

										if($toggle){
											$toggle = false;
											$row_style = 'class="alt"';
										}else{
											$toggle = true;
											$row_style = '';
										}

										$row_markup = '';
										$element_id = $data['element_id'];

										if($data['element_type'] == 'section' || $data['element_type'] == 'textarea') {
											if(!empty($data['label']) && !empty($data['value']) && ($data['value'] != '&nbsp;')){
												$section_separator = '<br/>';
											}else{
												$section_separator = '';
											}

											$section_break_content = '<span class="mf_section_title"><strong>'.nl2br($data['label']).'</strong></span>'.$section_separator.'<span class="mf_section_content">'.nl2br($data['value']).'</span>';

											$row_markup .= "<tr {$row_style}>\n";
											$row_markup .= "<td width=\"100%\" colspan=\"2\">{$section_break_content}</td>\n";
											$row_markup .= "</tr>\n";
										}else if($data['element_type'] == 'signature') {
											if($data['element_size'] == 'small'){
												$canvas_height = 70;
												$line_margin_top = 50;
											}else if($data['element_size'] == 'medium'){
												$canvas_height = 130;
												$line_margin_top = 95;
											}else{
												$canvas_height = 260;
												$line_margin_top = 200;
											}

											$signature_markup = <<<EOT
									        <div id="mf_sigpad_{$element_id}" class="mf_sig_wrapper {$data['element_size']}">
									          <canvas class="mf_canvas_pad" width="309" height="{$canvas_height}"></canvas>
									        </div>
									        <script type="text/javascript">
												$(function(){
													var sigpad_options_{$element_id} = {
										               drawOnly : true,
										               displayOnly: true,
										               bgColour: '#fff',
										               penColour: '#000',
										               output: '#element_{$element_id}',
										               lineTop: {$line_margin_top},
										               lineMargin: 10,
										               validateFields: false
										        	};
										        	var sigpad_data_{$element_id} = {$data['value']};
										      		$('#mf_sigpad_{$element_id}').signaturePad(sigpad_options_{$element_id}).regenerate(sigpad_data_{$element_id});
												});
											</script>
EOT;

											$row_markup .= "<tr>\n";
											$row_markup .= "<td width=\"40%\" style=\"vertical-align: top\"><strong>{$data['label']}</strong></td>\n";
											$row_markup .= "<td width=\"60%\">{$signature_markup}</td>\n";
											$row_markup .= "</tr>\n";
										}else{
											$row_markup .= "<tr {$row_style}>\n";
											$row_markup .= "<td width=\"40%\"><strong>{$data['label']}</strong></td>\n";
											$row_markup .= "<td width=\"60%\">".nl2br($data['value'])."</td>\n";
											$row_markup .= "</tr>\n";
										}

										echo $row_markup;
									} 
							?>  	
						  
						  </tbody>
						</table>
						
						<?php if(!empty($payment_enable_merchant)){ ?>
						<table width="100%" cellspacing="0" cellpadding="0" border="0" id="ve_payment_info">
							<tbody>		
								<tr>
							  	    <td class="payment_details_header">
							  	    	<span class="icon-info"></span>Payment Details</td>
							  		<td>&nbsp; </td>
							  	</tr> 
									
								<tr class="alt">
							  	    <td width="40%" class="payment_label"><strong>Amount</strong></td>
							  		<td width="60%"><?php echo $currency_symbol.$payment_amount.' '.$payment_currency; ?></td>
							  	</tr>  	
							  	<tr>
							  	    <td class="payment_label"><strong>Status</strong></td>
							  		<td class="payment_status_row">
							  			<span id="payment_status_static">	
								  			<span class="payment_status <?php echo $payment_status; ?>"><?php echo strtoupper($payment_status); ?></span> 
											<?php if(!empty($payment_test_mode)){ ?>
												<em style="margin-left: 5px">(TEST mode)</em>
											<?php } ?>
											<a href="#" class="blue_dotted status_changer" id="payment_status_change_link">change status</a>
										</span>
										<span id="payment_status_form" style="display: none">
											<select name="payment_status_dropdown" id="payment_status_dropdown" class="element select small"> 
												<option <?php if($payment_status == 'paid'){ echo 'selected="selected"'; } ?> value="paid">Paid</option>
												<option <?php if($payment_status == 'unpaid'){ echo 'selected="selected"'; } ?> value="unpaid">Unpaid</option>
												<option <?php if($payment_status == 'pending'){ echo 'selected="selected"'; } ?> value="pending">Pending</option>
												<option <?php if($payment_status == 'declined'){ echo 'selected="selected"'; } ?> value="declined">Declined</option>
												<option <?php if($payment_status == 'refunded'){ echo 'selected="selected"'; } ?> value="refunded">Refunded</option>
												<option <?php if($payment_status == 'cancelled'){ echo 'selected="selected"'; } ?> value="cancelled">Cancelled</option>	
											</select>
											<span id="payment_status_save_cancel"><a href="#" class="blue_dotted" id="payment_status_save_link" style="margin-left: 10px">save</a> or <a href="#" class="blue_dotted" id="payment_status_cancel_link">cancel</a></span>
											<span id="payment_status_loader" style="display: none"><em>saving...</em> <img align="absmiddle" src='images/loader_small_grey.gif' /></span>
										</span>
									</td>
							  	</tr>

							  	<?php if($payment_has_record){ ?>
									<tr class="alt">
								  	    <td class="payment_label"><strong>Payment ID</strong></td>
								  		<td><?php echo $payment_id; ?></td>
								  	</tr>
								  	<tr>
								  	    <td class="payment_label"><strong>Payment Date</strong></td>
								  		<td><?php echo $payment_date; ?></td>
								  	</tr>
								  	<tr class="alt">
								  	    <td>&nbsp;</td>
								  		<td>&nbsp;</td>
								  	</tr>
								  	<tr>
								  	    <td class="payment_label"><strong>Full Name</strong></td>
								  		<td><?php echo htmlspecialchars($payment_fullname,ENT_QUOTES); ?></td>
								  	</tr>
								  	
								  	<?php if(!empty($payment_ask_billing) && !empty($billing_address)){ ?>
								  	<tr class="alt">
								  	    <td class="payment_label"><strong>Billing Address</strong></td>
								  		<td><?php echo $billing_address; ?></td>
								  	</tr>
								  	<?php } ?>
								  	
								  	<?php if(!empty($payment_ask_shipping) && !empty($shipping_address)){ ?>
								  	<tr>
								  	    <td class="payment_label"><strong>Shipping Address</strong></td>
								  		<td><?php echo $shipping_address; ?></td>
								  	</tr>
								  	<?php } ?>
							  	
							  	<?php } ?>

							</tbody>
						</table>
						<?php } ?>

						<table width="100%" cellspacing="0" cellpadding="0" border="0" id="ve_table_info">
							<tbody>		
								<tr>
							  	    <td class="entry_info_header">
							  	    	<span class="icon-info"></span>Entry Info</td>
							  		<td>&nbsp; </td>
							  	</tr> 
									
								<tr class="alt">
							  	    <td width="40%"><strong>Date Created</strong></td>
							  		<td width="60%"><?php echo $date_created; ?></td>
							  	</tr>  	
							  	<tr>
							  	    <td><strong>Date Updated</strong></td>
							  		<td><?php echo $date_updated; ?></td>
							  	</tr>  	
								<tr class="alt">
							  	    <td><strong>IP Address</strong></td>
							  		<td><?php echo $ip_address; ?></td>
							  	</tr>
							</tbody>
						</table>

						<?php if($is_incomplete_entry){ ?>
							<table width="100%" cellspacing="0" cellpadding="0" border="0" id="ve_table_info">
								<tbody>		
									<tr>
								  	    <td style="font-size: 85%;color: #444; font-weight: bold">
								  	    	<img src="images/icons/227_blue.png" align="absmiddle" style="vertical-align: middle;margin-right: 5px">Resume URL</td>
								  		<td>&nbsp; </td>
								  	</tr> 
									<tr class="alt">
								  	    <td colspan="2"><a class="ve_resume_link" href="<?php echo $form_resume_url; ?>"><?php echo $form_resume_url; ?></a></td>
								  	</tr>  	
								</tbody>
							</table>
						<?php } ?>

						<?php if(!empty($payment_resume_url)){ ?>
							<table width="100%" cellspacing="0" cellpadding="0" border="0" id="ve_table_info">
								<tbody>		
									<tr>
								  	    <td style="font-size: 85%;color: #444; font-weight: bold">
								  	    	<img src="images/icons/227_blue.png" align="absmiddle" style="vertical-align: middle;margin-right: 5px">Payment URL</td>
								  		<td>&nbsp; </td>
								  	</tr> 
									<tr class="alt">
								  	    <td colspan="2"><a class="ve_resume_link" href="<?php echo $payment_resume_url; ?>">Open Payment Page</a></td>
								  	</tr>  	
								</tbody>
							</table>
						<?php } ?>

					</div>
					<div id="ve_actions">
						<div id="ve_entry_navigation">
							<a href="<?php echo "view_entry.php?form_id={$form_id}&entry_id={$entry_id}&nav=prev"; ?>" title="Previous Entry" style="margin-left: 1px"><span class="icon-arrow-left"></span></a>
							<a href="<?php echo "view_entry.php?form_id={$form_id}&entry_id={$entry_id}&nav=next"; ?>" title="Next Entry" style="margin-left: 5px"><span class="icon-arrow-right"></span></a>
						</div>
						<div id="ve_entry_actions" class="gradient_blue">
							<ul>
								
								<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_perms['edit_entries'])){ ?>
								<li style="border-bottom: 1px dashed #8EACCF"><a id="ve_action_edit" title="Edit Entry" href="<?php echo "edit_entry.php?form_id={$form_id}&entry_id={$entry_id}"; ?>"><span class="icon-pencil"></span>Edit</a></li>
								<?php } ?>

								<li style="border-bottom: 1px dashed #8EACCF"><a id="ve_action_email" title="Email Entry" href="#"><span class="icon-envelope-opened"></span>Email</a></li>
								<li style="border-bottom: 1px dashed #8EACCF"><a id="ve_action_print" title="Print Entry" href="javascript:window.print()"><span class="icon-print"></span>Print</a></li>
								
								<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_perms['edit_entries'])){ ?>
								<li><a id="ve_action_delete" title="Delete Entry" href="#"><span class="icon-remove"></span>Delete</a></li>
								<?php } ?>
								
							</ul>
						</div>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

<div id="dialog-confirm-entry-delete" title="Are you sure you want to delete this entry?" class="buttons" style="display: none">
	<span class="icon-bubble-notification"></span>
	<p id="dialog-confirm-entry-delete-msg">
		This action cannot be undone.<br/>
		<strong id="dialog-confirm-entry-delete-info">Data and files associated with this entry will be deleted.</strong><br/><br/>
	</p>				
</div>

<div id="dialog-email-entry" title="Email entry #<?php echo $entry_id; ?> to:" class="buttons" style="display: none"> 
	<form id="dialog-email-entry-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">	
		<ul>
			<li>
				<div>
					<input type="text" value="" class="text" name="dialog-email-entry-input" id="dialog-email-entry-input" />
				</div> 
				<div class="infomessage" style="padding-top: 5px;padding-bottom: 0px">Use commas to separate email addresses.</div>
			</li>
		</ul>
	</form>
</div>

<div id="dialog-entry-sent" title="Success!" class="buttons" style="display: none">
	<img src="images/icons/62_green_48.png" title="Success" /> 
	<p id="dialog-entry-sent-msg">
			The entry has been sent.
	</p>
</div>
 
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
<script type="text/javascript" src="js/view_entry.js"></script>
EOT;

	require('includes/footer.php'); 
?>
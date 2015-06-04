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
	
	//get form properties
	$query 	= "select 
					form_name,
					form_frame_height,
					form_captcha
			     from 
			     	 ".MF_TABLE_PREFIX."forms 
			    where 
			    	 form_id = ?";
	$params = array($form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		$row['form_name'] = mf_trim_max_length($row['form_name'],65);
		
		$form_name 	= htmlspecialchars($row['form_name']);
		$form_frame_height  = (int) $row['form_frame_height'];

		if(empty($row['form_captcha'])){
			$form_frame_height += 80;
		}else{
			$form_frame_height += 250;
		}
	}

	$ssl_suffix = mf_get_ssl_suffix();
	$form_embed_url 	= 'http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/embed.php?id='.$form_id;
	$machform_base_url 	= 'http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/';
	$jquery_url 		= 'http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/js/jquery.min.js';

	//construct javascript code
	$javascript_form_code =<<<EOT
<script type="text/javascript">
var __machform_url = '{$form_embed_url}';
var __machform_height = {$form_frame_height};
</script>
<div id="mf_placeholder"></div>
<script type="text/javascript" src="{$jquery_url}"></script>
<script type="text/javascript" src="{$machform_base_url}js/jquery.ba-postmessage.min.js"></script>
<script type="text/javascript" src="{$machform_base_url}js/machform_loader.js"></script>
EOT;

	//construct iframe code
	$iframe_form_code = '<iframe onload="javascript:parent.scrollTo(0,0);" height="'.$form_frame_height.'" allowTransparency="true" frameborder="0" scrolling="no" style="width:100%;border:none" src="http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/embed.php?id='.$form_id.'" title="'.$form_name.'"><a href="http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/view.php?id='.$form_id.'" title="'.$form_name.'">'.$form_name.'</a></iframe>';	
	
	//construct php embed code
	$current_dir 	  = rtrim(dirname($_SERVER['PHP_SELF']));
	if($current_dir == "/" || $current_dir == "\\"){
		$current_dir = '';
	}
	
	$absolute_dir_path = rtrim(dirname($_SERVER['SCRIPT_FILENAME'])); 

	$php_embed_form_code =<<<EOT
<?php
require("{$absolute_dir_path}/machform.php");
\$mf_param['form_id'] = {$form_id};
\$mf_param['base_path'] = 'http{$ssl_suffix}://{$_SERVER['HTTP_HOST']}{$current_dir}/';
display_machform(\$mf_param);
?>
EOT;

	//construct simple link code
	$simple_link_form_code = '<a href="http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/view.php?id='.$form_id.'" title="'.$form_name.'">'.$form_name.'</a>';

	//construct popup link code
	if($form_frame_height > 750){
		$popup_height = 750;
	}else{
		$popup_height = $form_frame_height;
	}
	$popup_link_form_code = '<a href="http'.$ssl_suffix.'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/view.php?id='.$form_id.'" onclick="window.open(this.href,  null, \'height='.$popup_height.', width=800, toolbar=0, location=0, status=0, scrollbars=1, resizable=1\'); return false;">'.$form_name.'</a>';




	$current_nav_tab = 'manage_forms';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post embed_code">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2><?php echo "<a class=\"breadcrumb\" href='manage_forms.php?id={$form_id}'>".$form_name.'</a>'; ?> <img src="images/icons/resultset_next.gif" /> Form Code</h2>
							<p>Integrate the form into your website page by using the code provided below</p>
						</div>	
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>
				<div class="content_body">
					<div id="ec_main_code" class="gradient_blue">
						<div id="ec_main_code_meta">
							<span class="icon-paste" style="font-size: 65px;display:block;margin-top:35px"></span>
							<h5>Javascript Code</h5>
						</div>
						<div id="ec_main_code_content">
							<div id="ec_code_javascript">
								<label class="choice" for="ec_javascript">Copy and Paste the Code Below into Your Website Page</label>
								<textarea readonly="readonly" onclick="javascript: this.select()" id="ec_javascript" class="element textarea medium ec_code_data"><?php echo $javascript_form_code; ?></textarea>
							</div>
							<div id="ec_code_iframe" style="display: none">
								<label class="choice" for="ec_iframe">Copy and Paste the Code Below into Your Website Page</label>
								<textarea readonly="readonly" onclick="javascript: this.select()" id="ec_iframe" class="element textarea medium ec_code_data"><?php echo $iframe_form_code; ?></textarea>
							</div>
							<div id="ec_code_php_file" style="display: none">
								<label class="choice">Download PHP File Below and Upload to Your Server</label>
								<div id="ec_php_download">
									<a href="embed_file_download.php?id=<?php echo $form_id; ?>">Download File</a>
								</div>
							</div>
							<div id="ec_code_php_code" style="display: none">
								<label class="choice" for="ec_iframe">Copy and Paste the Code Below into Your Website Page</label>
								<textarea readonly="readonly" onclick="javascript: this.select()" id="ec_php_code" class="element textarea medium ec_code_data"><?php echo $php_embed_form_code; ?></textarea>
							</div>
							<div id="ec_code_simple_link" style="display: none">
								<label class="choice" for="ec_iframe">Copy and Paste the Code Below into Your Website Page</label>
								<textarea readonly="readonly" onclick="javascript: this.select()" id="ec_simple_link" class="element textarea medium ec_code_data"><?php echo $simple_link_form_code; ?></textarea>
							</div>
							<div id="ec_code_popup_link" style="display: none">
								<label class="choice" for="ec_iframe">Copy and Paste the Code Below into Your Website Page</label>
								<textarea readonly="readonly" onclick="javascript: this.select()" id="ec_popup_link" class="element textarea medium ec_code_data"><?php echo $popup_link_form_code; ?></textarea>
							</div>
						</div>
					</div>
					<div id="ec_meta">
						<div id="ec_information" class="gradient_green">
							<img style="vertical-align: top" src="images/icons/68_green.png" class="helpmsg"> 
							<span id="ec_info_javascript">This code will insert the form into your existing web page seamlessly. Thus the form background, border and logo header won't be displayed.</span>
							<span id="ec_info_iframe" style="display:none">This code will insert the form into your existing web page seamlessly. Thus the form background, border and logo header won't be displayed. You might also need to adjust the iframe height value.</span>
							<span id="ec_info_php_file" style="display:none">This file will display your form without using any iframe. The file must be uploaded into the same server as your machform installation.</span>
							<span id="ec_info_php_code" style="display:none">This code will insert the form into your existing PHP pages without using any iframe. This code might not work on certain PHP pages, thus it's not guaranteed to work on all pages. In case of failure, use Javascript/Iframe Code instead.</span>
							<span id="ec_info_simple_link" style="display:none">This code will display direct link to your form. Use this code to share your form with others through emails or web pages.</span>
							<span id="ec_info_popup_link" style="display:none">This code will display your form into a popup window.</span>
						</div>
						<div id="ec_options" class="gradient_blue">
							<label for="ec_code_type" class="description">Form Code Type</label>
							<select class="element select medium" id="ec_code_type" name="ec_code_type" style="width: 210px"> 
								<option value="javascript">Javascript Code (Recommended)</option>
								<option value="iframe">Iframe Code</option>
								<option value="php_file">PHP Form File</option>
								<option value="php_code">PHP Embed Code</option>
								<option value="simple_link">Simple Link</option>
								<option value="popup_link">Popup Link</option>	
							</select>
						</div>
					</div>
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
<?php

	$footer_data =<<<EOT
<script type="text/javascript" src="js/embed_code.js"></script>
EOT;

	require('includes/footer.php'); 
?>
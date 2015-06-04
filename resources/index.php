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
	
	require('lib/password-hash.php');

	$ssl_suffix = mf_get_ssl_suffix();

	$dbh = mf_connect_db();
	
	//immediately redirect to installer page if the config values are correct but no ap_forms table found
	$query = "select count(*) from ".MF_TABLE_PREFIX."settings";
	$sth = $dbh->prepare($query);
	try{
		$sth->execute($params);
	}catch(PDOException $e) {
		header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/installer.php");
		exit;
	}
	
	$mf_settings = mf_get_settings($dbh);
	
	//first we need to check if the user has "remember me" cookie or not
	if(!empty($_COOKIE['mf_remember']) && empty($_SESSION['mf_logged_in'])){
		$query  = "SELECT 
						`user_id`,
						`priv_administer`,
						`priv_new_forms`,
						`priv_new_themes` 
					FROM 
						`".MF_TABLE_PREFIX."users` 
					WHERE 
						`cookie_hash`=? and `status`=1";
		$params = array($_COOKIE['mf_remember']);
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$user_id 			  = $row['user_id'];
		$priv_administer	  = (int) $row['priv_administer'];
		$priv_new_forms		  = (int) $row['priv_new_forms'];
		$priv_new_themes	  = (int) $row['priv_new_themes'];

		if(!empty($user_id)){
			$_SESSION['mf_logged_in'] = true;
			$_SESSION['mf_user_id']   = $user_id;
			$_SESSION['mf_user_privileges']['priv_administer'] = $priv_administer;
			$_SESSION['mf_user_privileges']['priv_new_forms']  = $priv_new_forms;
			$_SESSION['mf_user_privileges']['priv_new_themes'] = $priv_new_themes;
		}
	}
	
	//redirect to form manager if already logged-in
	if(!empty($_SESSION['mf_logged_in']) && $_SESSION['mf_logged_in'] == true){
		header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/manage_forms.php");
		exit;
	}
	
	if(!empty($_POST['submit'])){
		$username = strtolower(trim($_POST['admin_username']));
		$password = trim($_POST['admin_password']);
		$remember_me = trim($_POST['admin_remember']);

		if(empty($username) || empty($password)){
			$_SESSION['MF_LOGIN_ERROR'] = 'Incorrect email or password!';
		}else{
			$password_is_valid = false;

			//get the password hash from the database
			$query  = "SELECT 
							`user_password`,
							`user_id`,
							`priv_administer`,
							`priv_new_forms`,
							`priv_new_themes` 
						FROM 
							`".MF_TABLE_PREFIX."users` 
					   WHERE 
					   		`user_email`=? and `status`=1";
			$params = array($username);
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			$stored_password_hash = $row['user_password'];
			$user_id 			  = $row['user_id'];
			$priv_administer	  = (int) $row['priv_administer'];
			$priv_new_forms		  = (int) $row['priv_new_forms'];
			$priv_new_themes	  = (int) $row['priv_new_themes'];

			$hasher 	   = new PasswordHash(8, FALSE);
			$check_result  = $hasher->CheckPassword($password, $stored_password_hash);
			if($check_result){
				$password_is_valid = true;
			}

			if($password_is_valid){

				//regenerate session id for protection against session fixation
				session_regenerate_id();

				//set the session variables for the user=========
				$_SESSION['mf_logged_in'] = true;
				$_SESSION['mf_user_id']   = $user_id;
				$_SESSION['mf_user_privileges']['priv_administer'] = $priv_administer;
				$_SESSION['mf_user_privileges']['priv_new_forms']  = $priv_new_forms;
				$_SESSION['mf_user_privileges']['priv_new_themes'] = $priv_new_themes;
				//===============================================

				//update last_login_date and last_ip_address
				$last_login_date = date("Y-m-d H:i:s");
				$last_ip_address = $_SERVER['REMOTE_ADDR'];

				$query  = "UPDATE ".MF_TABLE_PREFIX."users set last_login_date=?,last_ip_address=? WHERE `user_id`=?";
				$params = array($last_login_date,$last_ip_address,$user_id);
				mf_do_query($query,$params,$dbh);


				//if the user select the "remember me option"
				//set the cookie and make it active for the next 30 days
				if(!empty($remember_me)){
					$cookie_hash = $hasher->HashPassword(mt_rand()); //generate random hash and save it into ap_users table

					$query = "update ".MF_TABLE_PREFIX."users set cookie_hash=? where `user_id`=?";
		   			$params = array($cookie_hash,$user_id);
		   			mf_do_query($query,$params,$dbh);

		   			//send the cookie
		   			setcookie('mf_remember',$cookie_hash, time()+3600*24*30, "/");
				}

				if(!empty($_SESSION['prev_referer'])){
					$next_page = $_SESSION['prev_referer'];
					
					unset($_SESSION['prev_referer']);
					header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$next_page);
					
					exit;
				}else{
					header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/manage_forms.php");
					exit;
				}
			}else{
				$_SESSION['MF_LOGIN_ERROR'] = 'Incorrect email or password!';
			}

		}

	}
	
	if(!empty($_GET['from'])){
		$_SESSION['prev_referer'] = base64_decode($_GET['from']);
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>MachForm Admin Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="index, nofollow" />
<link rel="stylesheet" type="text/css" href="css/main.css" media="screen" />   
    
<!--[if IE 7]>
	<link rel="stylesheet" type="text/css" href="css/ie7.css" media="screen" />
<![endif]-->
	
<!--[if IE 8]>
	<link rel="stylesheet" type="text/css" href="css/ie8.css" media="screen" />
<![endif]-->

<!--[if IE 9]>
	<link rel="stylesheet" type="text/css" href="css/ie9.css" media="screen" />
<![endif]-->
   
<link href="css/theme.css" rel="stylesheet" type="text/css" />
<?php
	if(!empty($mf_settings['admin_theme'])){
		echo '<link href="css/themes/theme_'.$mf_settings['admin_theme'].'.css" rel="stylesheet" type="text/css" />';
	}
?>
<link href="css/bb_buttons.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css" rel="stylesheet" />
<link type="text/css" href="css/edit_form.css" rel="stylesheet" />
<link type="text/css" href="js/datepick/smoothness.datepick.css" rel="stylesheet" />
<link href="css/override.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div id="bg" class="login_page">

<div id="container">

	<div id="header">
	<?php
		if(!empty($mf_settings['admin_image_url'])){
			$machform_logo_main = $mf_settings['admin_image_url'];
		}else{
			if(!empty($mf_settings['admin_theme'])){
				$machform_logo_main = 'images/machform_logo_'.$mf_settings['admin_theme'].'.png';
			}else{
				$machform_logo_main = 'images/machform_logo.png';
			}
		}
	?>
		<div id="logo">
			<img class="title" src="<?php echo $machform_logo_main; ?>" style="margin-left: 8px" alt="MachForm" />
		</div>	

		
		<div class="clear"></div>
		
	</div>
	<div id="main">
	
 
		<div id="content">
			<div class="post login_main">

				<div style="padding-top: 10px">
					
					<div>
						<img src="images/shield_128.png" align="absmiddle" style="width: 64px; height: 64px;float: left;padding-right: 5px"/>
						<h3>Sign In to Admin Panel</h3>
						<p>Sign in below to create or edit your forms</p>
						<div style="clear:both; border-bottom: 1px dotted #CCCCCC;margin-top: 15px"></div>
					</div>
					
					<div style="border-bottom: 1px dotted #CCCCCC;margin-top: 10px">
							<form id="form_login" class="appnitro"  method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
							<ul>

								<?php if(!empty($_SESSION['MF_LOGIN_ERROR'])){ ?>
									<li id="li_login_notification">
										<h5><?php echo $_SESSION['MF_LOGIN_ERROR']; ?></h5>	
									</li>		
								<?php 
									   unset($_SESSION['MF_LOGIN_ERROR']);
									} 
								?>

								<li id="li_email_address">
													
									<label class="desc" for="admin_username">Email Address</label>
									<div>
										<input id="admin_username" name="admin_username" class="element text large" type="text" maxlength="255" value="<?php echo htmlspecialchars($username); ?>"/> 
									</div>
									
								</li>		
								<li id="li_password">
									<label class="desc" for="admin_password">Password </label>
									<div>
										<input id="admin_password" name="admin_password" class="element text large" type="password" maxlength="255" value=""/> 
									</div> 
								</li>
								<li id="li_remember_me">
									<span>
										<input type="checkbox" value="1" class="element checkbox" name="admin_remember" id="admin_remember" style="margin-left: 0px">
										<label for="admin_remember" class="choice">Remember me</label>
							
									</span> 
								</li>
					    		<li id="li_submit" class="buttons" style="overflow: auto">
					    			<input type="hidden" name="submit" id="submit" value="1">
							    	<button type="submit" class="bb_button bb_green" id="submit_button" name="submit_button" style="float: left;border-radius: 4px">
								        <span class="icon-keyhole"></span>
								        Sign In
								    </button>
								</li>
							</ul>
							</form>	
					</div>
					<ul style="float: right;padding-top: 5px">
							<li>
									<span>
										<input type="checkbox" value="1" class="element checkbox" name="admin_forgot" id="admin_forgot" style="margin-left: 0px">
										<label for="admin_forgot" class="choice" style="color: #BD3D20;">I forgot my password</label>
							
									</span> 
							</li>
					</ul>
				</div>
     
        	</div>  		 
		</div>


<div id="dialog-login-page" title="Success!" class="buttons" style="display: none">
	<img src="images/icons/62_green_48.png" title="Success" /> 
	<p id="dialog-login-page-msg">
			Success
	</p>
</div>		
		

<?php
	$footer_data =<<<EOT
<script type="text/javascript" src="js/jquery.corner.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.core.js"></script>
<script type="text/javascript" src="js/login_admin.js"></script>
EOT;
	require('includes/footer.php'); 
?>
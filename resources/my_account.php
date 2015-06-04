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

	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	$user_id = $_SESSION['mf_user_id'];

	$query = "SELECT user_email,user_fullname FROM ".MF_TABLE_PREFIX."users WHERE user_id=? and `status`=1";
	$params = array($user_id);
			
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$stored_user_email    = $row['user_email'];
	$user_fullname = $row['user_fullname'];

	//handle form submission if there is any
	if(!empty($_POST['submit_form'])){

		$user_email = strtolower(trim($_POST['user_email']));
		
		//we need to check the email, ensure it's valid email address
		$email_regex  = '/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]*\.[A-z0-9]{2,6}$/';
		$regex_result = preg_match($email_regex, $user_email);
			
		if(empty($regex_result)){
			$_SESSION['MF_ERROR'] = 'Please enter valid email address!';
		}else{
			//check for duplicate
			$query = "select count(user_email) total_user from `".MF_TABLE_PREFIX."users` where user_email = ? and user_id <> ? and `status` > 0";
				
			$params = array($user_email,$user_id);
			$sth = mf_do_query($query,$params,$dbh);
			$row = mf_do_fetch_result($sth);

			if(!empty($row['total_user'])){
				$_SESSION['MF_ERROR'] = 'This email address already being used.';
			}else{

				//update the email address
				$query = "UPDATE ".MF_TABLE_PREFIX."users set user_email=? where user_id=?";
				$params = array($user_email,$user_id);
				mf_do_query($query,$params,$dbh);

				$_SESSION['MF_SUCCESS'] = 'Your profile has been saved.';
			}
		}

	}else{
		$user_email = $stored_user_email;
	}
	

		$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css" rel="stylesheet" />
EOT;

	$current_nav_tab = 'my_account';
	require('includes/header.php'); 
	
?>


		<div id="content" class="full">
			<div class="post main_settings">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2>My Account <img src="images/icons/resultset_next.gif" /> <?php echo htmlspecialchars($user_fullname); ?></h2>
							<p>Change your password or login email address.</p>
						</div>	
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">
					
					<form id="ms_form" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
					<ul id="ms_main_list">
						<li>
							<div id="ms_box_account" data-userid="<?php echo $user_id; ?>" class="ms_box_main gradient_blue">
								<div class="ms_box_title">
									<label class="choice">My Account Profile</label>
								</div>
								<div class="ms_box_email">
									<label class="description" for="user_email">Email Address <span class="required">*</span> <img class="helpmsg" src="images/icons/68_blue.png" style="vertical-align: top" title="This is the email address being used to login to the MachForm panel."/></label>
									<input id="user_email" name="user_email" class="element text medium" value="<?php echo htmlspecialchars($user_email,ENT_QUOTES); ?>" type="text">
									<a id="ms_change_password" href="#">Change Password</a>
								</div>
								<div>
								</div>
							</div>
						</li>
						<li>&nbsp;</li>
						<li style="padding-top: 20px">
							
							<a href="#" id="button_save_main_settings" class="bb_button bb_small bb_green">
								<span class="icon-disk" style="margin-right: 5px"></span>Save Changes
							</a>
							
						</li>		
					</ul>
					<input type="hidden" id="submit_form" name="submit_form" value="1">
					</form>

					<div id="dialog-change-password" title="Change My Password" class="buttons" style="display: none"> 
						<form id="dialog-change-password-form" class="dialog-form" style="margin-bottom: 10px">				
							<ul>
								<li>
									<label for="dialog-change-password-input1" class="description">Enter New Password</label>
									<input type="password" id="dialog-change-password-input1" name="dialog-change-password-input1" class="text large" value="">
									<label for="dialog-change-password-input2" style="margin-top: 15px" class="description">Confirm New Password</label>
									<input type="password" id="dialog-change-password-input2" name="dialog-change-password-input2" class="text large" value="">
									
								</li>
							</ul>
						</form>
					</div>

					<div id="dialog-password-changed" title="Success!" class="buttons" style="display: none">
						<img src="images/icons/62_green_48.png" title="Success" /> 
						<p id="dialog-password-changed-msg">
								The new password has been saved.
						</p>
					</div>

				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->

 
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
<script type="text/javascript" src="js/jquery.tools.min.js"></script>
<script type="text/javascript" src="js/my_account.js"></script>
EOT;

	require('includes/footer.php'); 
?>
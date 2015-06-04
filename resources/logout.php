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

	$dbh = mf_connect_db();
	
	$ssl_suffix = '';
	
	if(!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')){
		$ssl_suffix = 's';
	}else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){
	    $ssl_suffix = 's';
	}else if (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] == 'on'){
	    $ssl_suffix = 's';
	}else{
		$ssl_suffix = '';
	}
	
	$current_dir = dirname($_SERVER['PHP_SELF']);
    if($current_dir == "/" || $current_dir == "\\"){
		$current_dir = '';
	}
	
	$user_id  = $_SESSION['mf_user_id'];
	$_SESSION = array();

	setcookie('mf_remember','', time()-3600, "/"); //delete the remember me cookie
	$query = "update ".MF_TABLE_PREFIX."users set cookie_hash=? where user_id=?";
	$params = array('',$user_id);
	mf_do_query($query,$params,$dbh);

	header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].$current_dir."/index.php");
	exit;
?>
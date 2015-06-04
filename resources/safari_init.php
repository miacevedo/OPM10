<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2014 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('includes/init.php');

	$referrer = trim($_GET['ref']);
	$referrer = htmlspecialchars(base64_decode($referrer),ENT_QUOTES);

	setcookie("mf_safari_cookie_fix", "1", 0); //cookie expire at the end of session (browser being closed)

	header("Location: {$referrer}");
	exit;	
?>
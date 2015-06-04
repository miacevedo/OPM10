<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2014 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	session_start();

	date_default_timezone_set(@date_default_timezone_get());	
	error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
	
	@header("Content-Type: text/html; charset=UTF-8");
?>
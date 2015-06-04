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
	
	//get query string and parse it, query string is base64 encoded
	
	$query_string = trim($_GET['q']);
	parse_str(base64_decode($query_string),$params);
	
	$form_id 	= $params['form_id'];
	$id      	= $params['id'];
	$field_name = $params['el'];
	$signature_hash  = $params['hash'];
	
	
	if(empty($form_id) || empty($id) || empty($field_name) || empty($signature_hash)){
		die("Error. Incorrect URL.");
	}


	$dbh = mf_connect_db();

	$query 	= "select {$field_name} from `".MF_TABLE_PREFIX."form_{$form_id}` where id=?";
	$params = array($id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	$signature_data = $row[$field_name];

	if($signature_hash != md5($signature_data)){
		die("Error. Incorrect Signature URL.");
	}

?>
<!DOCTYPE html>
<head>
  <meta charset="utf-8">
  <title>Signature</title>
  <script type="text/javascript" src="js/jquery.min.js"></script>
  <!--[if lt IE 9]><script src="js/signaturepad/flashcanvas.js"></script><![endif]-->
  <script type="text/javascript" src="js/signaturepad/jquery.signaturepad.min.js"></script>
  <script type="text/javascript" src="js/signaturepad/json2.min.js"></script>
</head>
<body>
	<div id="mf_sigpad" class="mf_sig_wrapper">
		<canvas class="mf_canvas_pad" width="309" height="260"></canvas>
	</div>
	<script type="text/javascript">
		$(function(){
			var sigpad_options = {
				drawOnly : true,
				displayOnly: true,
				bgColour: '#fff',
				penColour: '#000',
				validateFields: false
			};
			var sigpad_data = <?php echo $signature_data; ?>;
			$('#mf_sigpad').signaturePad(sigpad_options).regenerate(sigpad_data);
		});
	</script>
</body>
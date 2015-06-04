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
	require('includes/entry-functions.php');
	require('includes/report-functions.php');

	$access_key = trim($_GET['key']);

	if(empty($access_key)){
		die("This widget is not available (missing access key).");
	}

	$dbh = mf_connect_db();
	
	//check the validity of the access key and get the chart property
	$query = "SELECT 
					chart_id,
					chart_type,
					chart_theme,
					chart_title,
					chart_title_align
			    FROM
			    	".MF_TABLE_PREFIX."report_elements
			   WHERE
			   		access_key = ? and chart_status = 1";
	$params = array($access_key);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	if(!empty($row['chart_id'])){
		$chart_type  = $row['chart_type'];
		$chart_title = htmlspecialchars($row['chart_title'],ENT_QUOTES);
		$chart_title_align = $row['chart_title_align'];
		$chart_theme = strtolower($row['chart_theme']);

		if(empty($chart_title)){
			$chart_title = 'Widget';
		}
	}else{
		die("This widget is no longer available (invalid access key).");
	}

	if($chart_type == 'grid'){
		$widget_markup = mf_display_grid($dbh,$access_key);
	}else{
		$widget_markup = mf_display_chart($dbh,$access_key);
	}


?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($chart_title); ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" >

   	<link href="js/kendoui/styles/kendo.common.min.css" rel="stylesheet">
    <link href="js/kendoui/styles/kendo.<?php echo $chart_theme; ?>.min.css" rel="stylesheet">
    
    <link href="js/kendoui/styles/kendo.dataviz.min.css" rel="stylesheet">
  	<link href="js/kendoui/styles/kendo.dataviz.<?php echo $chart_theme; ?>.min.css" rel="stylesheet">
    
    <script src="js/kendoui/js/jquery.min.js"></script>
    <script src="js/kendoui/js/kendo.custom.min.js"></script>
    
    <?php 
    	if($chart_type == 'grid'){ 
    		if(in_array($chart_theme, array('black','highcontrast','metroblack','moonlight'))){
    			$entry_link_color = '#ffffff';
    			$entry_link_border_bottom = '#ffffff';
    		}else{
    			$entry_link_color = '#3661A1';
    			$entry_link_border_bottom = '#000000';
    		}
    ?>
    <style>
		html {
		    font: 75% 'Lucida Sans Unicode','Lucida Grande',Arial,helvetica,sans-serif;
		}
		.me_center_div {
		    text-align: center !important;
		    width: 100%;
		}
		.me_right_div {
		    text-align: right !important;
		    width: 100%;
		}
		.me_file_div{
			background-image: url('images/icons/185.png');
			background-repeat: no-repeat;
			background-position: 0 2px;
			padding-left: 15px;
		}
		.entry_link{
			border-bottom: 1px dotted <?php echo $entry_link_border_bottom; ?>;
		    color: <?php echo $entry_link_color; ?> !important;
		    text-decoration: none !important;
		}
		.mf_grid_title{
			line-height: 30px;
			margin: 0px;
			font-size: 18px;
			font-family: Helvetica, Arial, sans-serif; 
			font-weight: 400;
			text-align: <?php echo $chart_title_align; ?>;
		}
	</style>
	<?php } ?>

</head>
<body style="margin: 0px">
    <?php echo $widget_markup; ?>
</body>
</html>
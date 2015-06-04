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
	require('includes/users-functions.php');
	
	$form_id 				= (int) trim($_POST['form_id']);
	$chart_id				= (int) trim($_POST['chart_id']);
	$chart_enable_filter	= (int) trim($_POST['chart_enable_filter']);
	$chart_labels_visible	= (int) trim($_POST['chart_labels_visible']);
	$chart_legend_visible	= (int) trim($_POST['chart_legend_visible']);
	$chart_tooltip_visible	= (int) trim($_POST['chart_tooltip_visible']);
	$chart_gridlines_visible = (int) trim($_POST['chart_gridlines_visible']);
	$chart_is_stacked 		= (int) trim($_POST['chart_is_stacked']);
	$chart_is_vertical 		= (int) trim($_POST['chart_is_vertical']);

	$chart_grid_page_size 	= (int) trim($_POST['chart_grid_page_size']);
	$chart_grid_max_length 	= (int) trim($_POST['chart_grid_max_length']);

	if(empty($chart_grid_page_size)){
		$chart_grid_page_size = 1;
	}

	$chart_height = trim($_POST['chart_height']);
	if($chart_height == 'custom'){
		$chart_height = (int) trim($_POST['chart_height_custom']);
	}else{
		$chart_height = (int) $chart_height;
	}

	$filter_properties_array = mf_sanitize($_POST['filter_prop']);
	$filter_type 			 = mf_sanitize($_POST['filter_type']);

	$chart_theme			 = mf_sanitize($_POST['chart_theme']);
	$chart_line_style		 = mf_sanitize($_POST['chart_line_style']);
	$chart_background		 = mf_sanitize($_POST['chart_background']);
	$chart_title	 		 = mf_sanitize($_POST['chart_title']);
	$chart_title_position    = mf_sanitize($_POST['chart_title_position']);
	$chart_title_align		 = mf_sanitize($_POST['chart_title_align']);

	$chart_labels_template	 = mf_sanitize($_POST['chart_labels_template']);
	$chart_labels_position   = mf_sanitize($_POST['chart_labels_position']);
	$chart_labels_align   	 = mf_sanitize($_POST['chart_labels_align']);

	$chart_legend_position   = mf_sanitize($_POST['chart_legend_position']);
	$chart_tooltip_template	 = mf_sanitize($_POST['chart_tooltip_template']);

	$chart_bar_color		 = mf_sanitize($_POST['chart_bar_color']);

	$chart_date_range		 = mf_sanitize($_POST['chart_date_range']);
	$chart_date_period_value = mf_sanitize($_POST['chart_date_period_value']);
	$chart_date_period_unit  = mf_sanitize($_POST['chart_date_period_unit']);
	$chart_date_axis_baseunit_period = mf_sanitize($_POST['chart_date_axis_baseunit_period']);
	$chart_date_axis_baseunit_custom = mf_sanitize($_POST['chart_date_axis_baseunit_custom']);

	if($chart_date_range == 'period'){
		$chart_date_axis_baseunit = $chart_date_axis_baseunit_period;
	}else if($chart_date_range == 'custom'){
		$chart_date_axis_baseunit = $chart_date_axis_baseunit_custom;
	}

	$chart_date_range_start = mf_sanitize($_POST['chart_date_range_start']); //format: mm/dd/yyyy
	$chart_date_range_end   = mf_sanitize($_POST['chart_date_range_end']); //format: mm/dd/yyyy

	$grid_column_preferences = mf_sanitize($_POST['grid_columns']);

	//convert into yyyy-mm-dd
	if(!empty($chart_date_range_start)){
		$exploded = array();
		$exploded = explode('/', $chart_date_range_start);
		$chart_date_range_start = $exploded[2].'-'.$exploded[0].'-'.$exploded[1];
	}

	//convert into yyyy-mm-dd
	if(!empty($chart_date_range_end)){
		$exploded = array();
		$exploded = explode('/', $chart_date_range_end);
		$chart_date_range_end = $exploded[2].'-'.$exploded[0].'-'.$exploded[1];
	}
	
	if(empty($form_id)){
		die("This file can't be opened directly.");
	}


	$dbh = mf_connect_db();
	
	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_form permission
		if(empty($user_perms['edit_form'])){
			die("Access Denied. You don't have permission to edit this form.");
		}
	}

	/***************************************************************************************************************/	
	/* 1. Save Widget Data settings																   				   */
	/***************************************************************************************************************/
	
	//save filters
	if(!empty($chart_enable_filter)){
		//first delete all previous filter
		$query = "delete from `".MF_TABLE_PREFIX."report_filters` where form_id=? and chart_id=?";
		$params = array($form_id,$chart_id);
		mf_do_query($query,$params,$dbh);
		
		//save the new filters
		$query = "insert into `".MF_TABLE_PREFIX."report_filters`(form_id,chart_id,element_name,filter_condition,filter_keyword) values(?,?,?,?,?)";

		foreach($filter_properties_array as $data){
			$params = array($form_id,$chart_id,$data['element_name'],$data['condition'],$data['keyword']);
			mf_do_query($query,$params,$dbh);
		}
	}

	$query  = "UPDATE ".MF_TABLE_PREFIX."report_elements 
				   SET 
				   	  chart_enable_filter = ?,
				   	  chart_filter_type = ?,
				   	  chart_theme = ?,
				   	  chart_background = ?,
				   	  chart_title = ?,
				   	  chart_title_position = ?,
				   	  chart_title_align = ?,
				   	  chart_labels_template = ?,
				   	  chart_labels_visible = ?,
				   	  chart_labels_position = ?,
				   	  chart_labels_align = ?,
				   	  chart_legend_visible = ?,
				   	  chart_legend_position = ?,
				   	  chart_tooltip_template = ?,
				   	  chart_tooltip_visible = ?,
				   	  chart_gridlines_visible = ?,
				   	  chart_is_stacked = ?,
				   	  chart_is_vertical = ?,
				   	  chart_bar_color = ?,
				   	  chart_line_style = ?,
				   	  chart_date_range = ?,
				   	  chart_date_period_value = ?,
				   	  chart_date_period_unit = ?,
				   	  chart_date_axis_baseunit = ?,
				   	  chart_date_range_start = ?,
				   	  chart_date_range_end = ?,
				   	  chart_grid_page_size = ?,
				   	  chart_grid_max_length = ?,
				   	  chart_height = ?   
				 WHERE 
				 	  form_id = ? and chart_id = ?";
	$params = array($chart_enable_filter,
					$filter_type,
					$chart_theme,
					$chart_background,
					$chart_title,
					$chart_title_position,
					$chart_title_align,
					$chart_labels_template,
					$chart_labels_visible,
					$chart_labels_position,
					$chart_labels_align,
					$chart_legend_visible,
					$chart_legend_position,
					$chart_tooltip_template,
					$chart_tooltip_visible,
					$chart_gridlines_visible,
					$chart_is_stacked,
					$chart_is_vertical,
					$chart_bar_color,
					$chart_line_style,
					$chart_date_range,
					$chart_date_period_value,
					$chart_date_period_unit,
					$chart_date_axis_baseunit,
					$chart_date_range_start,
					$chart_date_range_end,
					$chart_grid_page_size,
					$chart_grid_max_length,
					$chart_height,

					$form_id,$chart_id);
	mf_do_query($query,$params,$dbh);

	//if this is grid, save column preferences
	$query = "SELECT 
					chart_type
			    FROM
			    	".MF_TABLE_PREFIX."report_elements
			   WHERE
			   		form_id = ? and chart_id = ?";
	$params = array($form_id,$chart_id);
		
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$chart_type  = $row['chart_type'];
	
	if($chart_type == 'grid'){
		//first delete all previous preferences
		$query = "delete from `".MF_TABLE_PREFIX."grid_columns` where form_id=? and chart_id=?";
		$params = array($form_id,$chart_id);
		mf_do_query($query,$params,$dbh);

		//save the new preference
		$query = "insert into `".MF_TABLE_PREFIX."grid_columns`(form_id,chart_id,element_name,position) values(?,?,?,?)";

		$position = 1;
		if(!empty($grid_column_preferences)){
			foreach($grid_column_preferences as $data){
				$column_name = $data['name'];
				
				$params = array($form_id,$chart_id,$column_name,$position);
				mf_do_query($query,$params,$dbh);

				$position++;
			}
		}
	}

	
	$_SESSION['MF_SUCCESS'] = 'Widget settings has been saved.';
	
	$response_data = new stdClass();
	$response_data->status    	= "ok";
	$response_data->form_id 	= $form_id;
	
	$response_json = json_encode($response_data);
	
	echo $response_json;
?>
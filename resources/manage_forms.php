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
	require('includes/users-functions.php');

	$dbh 		 = mf_connect_db();

	$mf_settings = mf_get_settings($dbh);
	$selected_form_id = (int) $_GET['id'];

	$user_permissions = mf_get_user_permissions_all($dbh,$_SESSION['mf_user_id']);
	
	if(!empty($_GET['hl'])){
		$highlight_selected_form_id = true;
	}else{
		$highlight_selected_form_id = false;
	}
	
	//determine the sorting order
	$form_sort_by = 'date_created'; //the default sort order
	
	if(!empty($_GET['sortby'])){
		$form_sort_by = strtolower(trim($_GET['sortby'])); //the user select a new sort order
		
		//save the sort order into ap_form_sorts table
		$query = "delete from ".MF_TABLE_PREFIX."form_sorts where user_id=?";
		$params = array($_SESSION['mf_user_id']);
		mf_do_query($query,$params,$dbh);

		$query = "insert into ".MF_TABLE_PREFIX."form_sorts(user_id,sort_by) values(?,?)";
		$params = array($_SESSION['mf_user_id'],$form_sort_by);
		mf_do_query($query,$params,$dbh);
		
	}else{ //load the previous saved sort order

		$query = "select sort_by from ".MF_TABLE_PREFIX."form_sorts where user_id=?";
		$params = array($_SESSION['mf_user_id']);
	
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		if(!empty($row)){
			$form_sort_by = $row['sort_by'];
		}
	} 
	
	$query_order_by_clause = '';
	
	if($form_sort_by == 'form_title'){
		$query_order_by_clause = " ORDER BY form_name ASC";
		$sortby_title = 'Form Title';
	}else if($form_sort_by == 'form_tags'){
		$query_order_by_clause = " ORDER BY form_tags ASC";
		$sortby_title = 'Form Tags';
	}else if($form_sort_by == 'today_entries'){
		$sortby_title = "Today's Entries";
		
		
	}else if($form_sort_by == 'total_entries'){
		$sortby_title = "Total Entries";
		
		
	}else{ //the default date created sort
		$query_order_by_clause = " ORDER BY form_id ASC";
		$sortby_title = "Date Created";
	}
	
	//the number of forms being displayed on each page
	$rows_per_page = $mf_settings['form_manager_max_rows'];  
	
	//get the list of the form, put them into array
	$query = "SELECT 
					form_name,
					form_id,
					form_tags,
					form_active,
					form_disabled_message,
					form_theme_id
				FROM
					".MF_TABLE_PREFIX."forms
				WHERE
					form_active=0 or form_active=1
					{$query_order_by_clause}";
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);
	
	$form_list_array = array();
	$i=0;
	while($row = mf_do_fetch_result($sth)){
		
		//check user permission to this form
		if(empty($_SESSION['mf_user_privileges']['priv_administer']) && empty($user_permissions[$row['form_id']])){
			continue;
		}
		
		$form_list_array[$i]['form_id']   	  = $row['form_id'];

		$row['form_name'] = mf_trim_max_length($row['form_name'],75);

		if(!empty($row['form_name'])){		
			$form_list_array[$i]['form_name'] = $row['form_name'];
		}else{
			$form_list_array[$i]['form_name'] = '-Untitled Form- (#'.$row['form_id'].')';
		}	
		
		$form_list_array[$i]['form_active']   			= $row['form_active'];
		$form_list_array[$i]['form_disabled_message']   = $row['form_disabled_message'];
		$form_list_array[$i]['form_theme_id'] 			= $row['form_theme_id'];
		
		$form_disabled_message = json_encode($row['form_disabled_message']);
		$jquery_data_code .= "\$('#liform_{$row['form_id']}').data('form_disabled_message',{$form_disabled_message});\n";

		//get todays entries count
		$sub_query = "select count(*) today_entry from `".MF_TABLE_PREFIX."form_{$row['form_id']}` where `status`=1 and date_created >= date_format(curdate(),'%Y-%m-%d 00:00:00') ";
		$sub_sth = mf_do_query($sub_query,array(),$dbh);
		$sub_row = mf_do_fetch_result($sub_sth);
		
		$form_list_array[$i]['today_entry'] = $sub_row['today_entry'];
		
		//get latest entry timing
		if(!empty($sub_row['today_entry'])){
			$sub_query = "select date_created from `".MF_TABLE_PREFIX."form_{$row['form_id']}` order by id desc limit 1";
			$sub_sth = mf_do_query($sub_query,array(),$dbh);
			$sub_row = mf_do_fetch_result($sub_sth);
			
			$form_list_array[$i]['latest_entry'] = mf_relative_date($sub_row['date_created']);
		}
		
		//get total entries count
		if($form_sort_by == 'total_entries'){
			$sub_query = "select count(*) total_entry from `".MF_TABLE_PREFIX."form_{$row['form_id']}` where `status`=1";
			$sub_sth = mf_do_query($sub_query,array(),$dbh);
			$sub_row = mf_do_fetch_result($sub_sth);
			
			$form_list_array[$i]['total_entry'] = $sub_row['total_entry'];
		}
		
		
		//get form tags and split them into array
		if(!empty($row['form_tags'])){
			$form_tags_array = explode(',',$row['form_tags']);
			array_walk($form_tags_array, 'mf_trim_value');
			$form_list_array[$i]['form_tags'] = $form_tags_array;
		}
		
		$i++;
	}
	
	
	if($form_sort_by == 'today_entries'){
		usort($form_list_array, 'sort_by_today_entry');
	}
	
	if($form_sort_by == 'total_entries'){
		usort($form_list_array, 'sort_by_total_entry');
	}

	
	if(empty($selected_form_id)){ //if there is no preference for which form being displayed, display the first form
		$selected_form_id = $form_list_array[0]['form_id'];
	}

	$selected_page_number = 1;
	
	//build pagination markup
	$total_rows = count($form_list_array);
	$total_page = ceil($total_rows / $rows_per_page);
	
	if($total_page > 1){
		
		$start_form_index = 0;
		$pagination_markup = '<ul id="mf_pagination" class="pages green small">'."\n";
		
		for($i=1;$i<=$total_page;$i++){
			
			//attach the data code into each pagination button
			$end_form_index = $start_form_index + $rows_per_page;
			$liform_ids_array = array();
			
			for ($j=$start_form_index;$j<$end_form_index;$j++) {
				if(!empty($form_list_array[$j]['form_id'])){
					$liform_ids_array[] = '#liform_'.$form_list_array[$j]['form_id'];
					
					//put the page number into the array
					$form_list_array[$j]['page_number'] = $i;
					
					//we need to determine on which page the selected_form_id being displayed
					if($selected_form_id == $form_list_array[$j]['form_id']){
						$selected_page_number = $i;
					}
				}
			}
			
			$liform_ids_joined = implode(',',$liform_ids_array);
			$start_form_index = $end_form_index;
			
			$jquery_data_code .= "\$('#pagebtn_{$i}').data('liform_list','{$liform_ids_joined}');\n";
			
			
			if($i == $selected_page_number){
				if($selected_page_number > 1){
					$pagination_markup = str_replace('current_page','',$pagination_markup);
				}
				
				$pagination_markup .= '<li id="pagebtn_'.$i.'" class="page current_page">'.$i.'</li>'."\n";
			}else{
				$pagination_markup .= '<li id="pagebtn_'.$i.'" class="page">'.$i.'</li>'."\n";
			}
			
		}
		
		$pagination_markup .= '</ul>';
	}else{
		//if there is only 1 page, set the page_number property for each form to 1
		foreach ($form_list_array as $key=>$value){
			$form_list_array[$key]['page_number'] = 1;
		}
	}

	//get the available tags
	$query = "select form_tags from ".MF_TABLE_PREFIX."forms where form_tags is not null and form_tags <> ''";
	$params = array();
	
	$sth = mf_do_query($query,$params,$dbh);
	$raw_tags = array();
	while($row = mf_do_fetch_result($sth)){
		$raw_tags = array_merge(explode(',',$row['form_tags']),$raw_tags);
	}

	$all_tagnames = array_unique($raw_tags);
	sort($all_tagnames);
	
	$jquery_data_code .= "\$('#dialog-enter-tagname-input').data('available_tags',".json_encode($all_tagnames).");\n";
	
	//get the available custom themes
	if(!empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$query = "SELECT theme_id,theme_name FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=0 and status=1 ORDER BY theme_name ASC";
		$params = array();
	}else{
		$query = "SELECT 
						theme_id,
						theme_name 
					FROM 
						".MF_TABLE_PREFIX."form_themes 
				   WHERE 
					   	(theme_built_in=0 and status=1 and user_id=?) OR
					   	(theme_built_in=0 and status=1 and user_id <> ? and theme_is_private=0)
				ORDER BY 
						theme_name ASC";
		$params = array($_SESSION['mf_user_id'],$_SESSION['mf_user_id']);
	}	
	
	$sth = mf_do_query($query,$params,$dbh);

	$theme_list_array = array();
	while($row = mf_do_fetch_result($sth)){
		$theme_list_array[$row['theme_id']] = htmlspecialchars($row['theme_name']);
	}

	//get built-in themes
	$query = "SELECT theme_id,theme_name FROM ".MF_TABLE_PREFIX."form_themes WHERE theme_built_in=1 and status=1 ORDER BY theme_name ASC";
		
	$params = array();
	$sth = mf_do_query($query,$params,$dbh);

	$theme_builtin_list_array = array();
	while($row = mf_do_fetch_result($sth)){
		$theme_builtin_list_array[$row['theme_id']] = htmlspecialchars($row['theme_name']);
	}
	
		$header_data =<<<EOT
<link type="text/css" href="js/jquery-ui/themes/base/jquery.ui.all.css" rel="stylesheet" />
<link type="text/css" href="css/pagination_classic.css" rel="stylesheet" />
<link type="text/css" href="css/dropui.css" rel="stylesheet" />
EOT;

		
		
		
	
	$current_nav_tab = 'manage_forms';
	
	require('includes/header.php'); 
	
?>

		<div id="content" class="full">
			<div class="post manage_forms">
				<div class="content_header">
					<div class="content_header_title">
						<div style="float: left">
							<h2>Form Manager</h2>
							<p>Create, edit and manage your forms</p>
						</div>
						
						<?php if(!empty($_SESSION['mf_user_privileges']['priv_new_forms'])){ ?>
						<div style="float: right;margin-right: 5px">
								<a href="edit_form.php" id="button_create_form" class="bb_button bb_small bb_green">
									<span class="icon-file3" style="margin-right: 5px"></span>Create New Form!
								</a>
						</div>
						<?php } ?>

						<div style="clear: both; height: 1px"></div>
					</div>
				</div>
				
				<?php mf_show_message(); ?>
				
				<div class="content_body">
				<?php if(!empty($form_list_array)){ ?>	
					<div id="mf_top_pane">
						<div id="mf_search_pane">
							<div id="mf_search_box" class="">
								<input name="filter_form_input" id="filter_form_input" type="text" class="text" value="find form..."/>
								<div id="mf_search_title" class="mf_pane_selected"><a href="#">&#8674; form title</a></div>
								<div id="mf_search_tag"><a href="#">form tags</a></div>
							</div>
						</div>
						<div id="mf_filter_pane">
							<div class="dropui dropuiquick dropui-menu dropui-pink dropui-right">
								<a href="javascript:;" class="dropui-tab">
									Sort By &#8674; <?php echo $sortby_title; ?>
								</a>
							
								<div class="dropui-content">
									<ul>
										<li <?php if($form_sort_by == 'date_created'){ echo 'class="sort_active"'; } ?>><a id="sort_date_created_link" href="manage_forms.php?sortby=date_created">Date Created</a></li>
										<li <?php if($form_sort_by == 'form_title'){ echo 'class="sort_active"'; } ?>><a id="sort_form_title_link" href="manage_forms.php?sortby=form_title">Form Title</a></li>
										<li <?php if($form_sort_by == 'form_tags'){ echo 'class="sort_active"'; } ?>><a id="sort_form_tag_link" href="manage_forms.php?sortby=form_tags">Form Tags</a></li>
										<li <?php if($form_sort_by == 'today_entries'){ echo 'class="sort_active"'; } ?>><a id="sort_today_entries_link" href="manage_forms.php?sortby=today_entries">Today's Entries</a></li>
										<li <?php if($form_sort_by == 'total_entries'){ echo 'class="sort_active"'; } ?>><a id="sort_total_entries_link" href="manage_forms.php?sortby=total_entries">Total Entries</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div id="filtered_result_box">
						<div style="float: left">Filtered Results for &#8674; <span class="highlight"></span></div>
						<div id="filtered_result_box_right">
							<ul>
								<li><a href="#" id="mf_filter_reset" title="Clear filter"><img src="images/icons/56.png" /></a></li>
								<li id="filtered_result_total">Found 0 forms</li>
							</ul>
						</div>
					</div>
					<div id="filtered_result_none">
						Your filter did not match any of your forms.
					</div>
					<ul id="mf_form_list">
					<?php 
						
						$row_num = 1;
						
						foreach ($form_list_array as $form_data){
							$form_name   	 = htmlspecialchars($form_data['form_name']);
							$form_id   	 	 = $form_data['form_id'];
							$today_entry 	 = $form_data['today_entry'];
							$total_entry 	 = $form_data['total_entry'];
							$latest_entry 	 = $form_data['latest_entry'];
							$theme_id		 = (int) $form_data['form_theme_id'];
							
							if(!empty($form_data['form_tags'])){
								$form_tags_array = array_reverse($form_data['form_tags']);
							}else{
								$form_tags_array = array();
							}
							
							
							$form_class = array();
							$form_class_tag = '';
							
							if($form_id == $selected_form_id){
								$form_class[] = 'form_selected';
							}
							
							if(empty($form_data['form_active'])){
								$form_class[] = 'form_inactive';
							}
							
							if($selected_page_number == $form_data['page_number']){
								$form_class[] = 'form_visible';
							}
							
							$form_class_joined = implode(' ',$form_class);
							$form_class_tag	   = 'class="'.$form_class_joined.'"';
							
							
					?>
					
						<li data-theme_id="<?php echo $theme_id; ?>" id="liform_<?php echo $form_id; ?>" <?php echo $form_class_tag; ?>>
							
							<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
							<div class="form_option mf_link_delete">
								<a href="#"><span class="icon-remove"></span>Delete</a>
							</div>
							<?php } ?>

							<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($_SESSION['mf_user_privileges']['priv_new_forms'])){ ?>
							<div class="form_option mf_link_duplicate">
								<a href="#"><span class="icon-files"></span>Duplicate</a>
							</div>
							<?php } ?>
							
							<div style="height: 0px; clear: both;"></div>
							<div class="middle_form_bar">
								<h3><span class="icon-file2"></span><?php echo $form_name; ?></h3>
								<div class="form_meta">
									
									<?php if(!empty($total_entry)){ ?>
									<div class="form_stat form_stat_total" title="<?php echo $today_entry." entries today. Latest entry ".$latest_entry."."; ?>">
										<div class="form_stat_count"><?php echo $total_entry; ?></div>
										<div class="form_stat_msg">total</div>
									</div>
									<?php }else if(!empty($today_entry)){ ?>
									<div class="form_stat" title="<?php echo $today_entry." entries today. Latest entry ".$latest_entry."."; ?>">
										<div class="form_stat_count"><?php echo $today_entry; ?></div>
										<div class="form_stat_msg">today</div>
									</div>
									<?php } ?>
									
									<div class="form_tag">
										<ul class="form_tag_list">
											<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
											<li class="form_tag_list_icon"><a title="Add a Tag Name" class="addtag" id="addtag_<?php echo $form_id; ?>" href="#"><span class="icon-tags"></span></a></li>
											<?php } ?>

											<?php 	
												if(!empty($form_tags_array)){
													foreach ($form_tags_array as $tagname){
														echo "<li>".htmlspecialchars($tagname)." <a class=\"removetag\" href=\"#\" title=\"Remove this tag.\"><span class=\"icon-cancel-circle\"></span></a></li>";
													}
												}
											?>
											
										</ul>
									</div>
								</div>
								<div style="height: 0px; clear: both;"></div>
							</div>
							
							<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_entries']) || !empty($user_permissions[$form_id]['view_entries'])){ ?>
							<div class="form_option mf_link_entries">
								<a href="manage_entries.php?id=<?php echo $form_id; ?>"><span class="icon-database"></span>Entries</a>
							</div>
							<?php } ?>
							
							<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
							<div class="form_option mf_link_edit">
								<a href="edit_form.php?id=<?php echo $form_id; ?>"><span class="icon-pencil"></span>Edit</a>
							</div>
							<?php } ?>

							<div class="form_option mf_link_group">
								<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
								<a class="mf_link_theme" href="#"><span class="icon-palette2"></span>Theme</a>
								<a class="mf_link_emails" href="notification_settings.php?id=<?php echo $form_id; ?>"><span class="icon-envelope-opened"></span>Notifications</a>
								<a class="mf_link_code" href="embed_code.php?id=<?php echo $form_id; ?>"><span class="icon-paste"></span>Code</a>
								<a class="mf_link_payment" href="payment_settings.php?id=<?php echo $form_id; ?>"><span class="icon-cart"></span>Payment</a>
								<a class="mf_link_logic" href="logic_settings.php?id=<?php echo $form_id; ?>"><span class="icon-shuffle"></span>Logic</a>
								<a class="mf_link_report" href="manage_report.php?id=<?php echo $form_id; ?>"><span class="icon-chart"></span>Report</a>
								<?php } ?>

								<a class="mf_link_view" target="_blank" href="view.php?id=<?php echo $form_id; ?>"><span class="icon-search"></span>View</a>
							</div>
							
							<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($user_permissions[$form_id]['edit_form'])){ ?>
							<div class="form_option mf_link_disable">
								<?php 
									if(empty($form_data['form_active'])){
										echo '<a href="#"><span class="icon-play"></span>Enable</a>';	
									}else{
										echo '<a href="#"><span class="icon-pause"></span>Disable</a>';	
									}
								?>
							</div>
							<?php } ?>
							
							<div style="height: 0px; clear: both;"></div>
						</li>
						
					<?php 
							$row_num++; 
						}//end foreach $form_list_array 
					?>
						
					</ul>
					
					<div id="result_set_show_more">
						<a href="#">Show More Results...</a>
					</div>
					
					<!-- start pagination -->
					
					<?php echo $pagination_markup; ?>
					
					<!-- end pagination -->
					<?php }else{ ?>
							
							<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer']) || !empty($_SESSION['mf_user_privileges']['priv_new_forms'])){ ?>
							
							<div id="form_manager_empty">
								<img src="images/icons/arrow_up.png" />
								<h2>Welcome!</h2>
								<h3>You have no forms yet. Go create one by clicking the button above.</h3>
							</div>
							
							<?php } else{ ?>

							<div id="form_manager_empty">
								<h2 style="padding-top: 135px">Welcome!</h2>
								<h3>You currently have no access to any forms.</h3>
							</div>

							<?php } ?>	
					
					<?php } ?>
					
					
					<!-- start dialog boxes -->
					<div id="dialog-enter-tagname" title="Enter a Tag Name" class="buttons" style="display: none"> 
						<form id="dialog-enter-tagname-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
							<ul>
								<li>
									<div>
									<input type="text" value="" class="text" name="dialog-enter-tagname-input" id="dialog-enter-tagname-input" />
									<div class="infomessage"><img src="images/icons/70_green.png" style="vertical-align: middle"/> Tag name is optional. Use it when you have many forms, to group them into categories.</div>
									</div> 
								</li>
							</ul>
						</form>
					</div>
					<div id="dialog-confirm-form-delete" title="Are you sure you want to delete this form?" class="buttons" style="display: none">
						<span class="icon-bubble-notification"></span>
						<p>
							This action cannot be undone.<br/>
							<strong>All data and files collected by <span id="confirm_form_delete_name">this form</span> will be deleted as well.</strong><br/><br/>
							
						</p>
						
					</div>
					<div id="dialog-change-theme" title="Select a Theme" class="buttons" style="display: none"> 
						<form id="dialog-change-theme-form" class="dialog-form" style="padding-left: 10px;padding-bottom: 10px">				
							<ul>
								<li>
									<div>
										<select class="select full" id="dialog-change-theme-input" name="dialog-change-theme-input">
										<?php if(!empty($theme_list_array) || !empty($_SESSION['mf_user_privileges']['priv_new_themes'])){ ?>	
											<optgroup label="Your Themes">
												<?php 
													if(!empty($theme_list_array)){
														foreach ($theme_list_array as $theme_id=>$theme_name){
															echo "<option value=\"{$theme_id}\">{$theme_name}</option>";
														}
													}
												?>
												<?php if(!empty($_SESSION['mf_user_privileges']['priv_new_themes'])){ ?>
													<option value="new">&#8674; Create New Theme!</option>
												<?php } ?>
											</optgroup>
										<?php } ?>
											<optgroup label="Built-in Themes">
												<option value="0">White (Default)</option>
												<?php 
													if(!empty($theme_builtin_list_array)){
														foreach ($theme_builtin_list_array as $theme_id=>$theme_name){
															echo "<option value=\"{$theme_id}\">{$theme_name}</option>";
														}
													}
												?>
											</optgroup>
										</select>
									</div> 
								</li>
							</ul>
						</form>
					</div>
					<div id="dialog-disabled-message" title="Please Enter a Message" class="buttons" style="display: none"> 
						<form class="dialog-form">				
							<ul>
								<li>
									<label for="dialog-disabled-message-input" class="description">Your form will be closed and the message below will be displayed:</label>
									<div>
										<textarea cols="90" rows="8" class="element textarea medium" name="dialog-disabled-message-input" id="dialog-disabled-message-input"></textarea>
									</div>
								</li>
							</ul>
						</form>
					</div>
					<!-- end dialog boxes -->
				
				</div> <!-- /end of content_body -->	
			
			</div><!-- /.post -->
		</div><!-- /#content -->


 
<?php

	if($highlight_selected_form_id == true){
		$highlight_selected_form_id = $selected_form_id;
	}else{
		$highlight_selected_form_id = 0;
	}

	$footer_data =<<< EOT
<script type="text/javascript">
	var selected_form_id_highlight = {$highlight_selected_form_id};
	$(function(){
		{$jquery_data_code}		
    });
</script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.sortable.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.draggable.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.core.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.scale.js"></script>
<script type="text/javascript" src="js/jquery-ui/ui/jquery.effects.highlight.js"></script>
<script type="text/javascript" src="js/jquery.highlight.js"></script>
<script type="text/javascript" src="js/form_manager.js"></script>
EOT;

	require('includes/footer.php');
	
	
	/**** Helper Functions *******/
	
	function sort_by_today_entry($a, $b) {
    	return $b['today_entry'] - $a['today_entry'];
	}
	
	function sort_by_total_entry($a, $b) {
    	return $b['total_entry'] - $a['total_entry'];
	}
	
?>
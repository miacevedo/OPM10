//code for overloading the :contains selector to be case insensitive
jQuery.expr[':'].Contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};
jQuery.expr[':'].contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};

var selected_form_id = null; 

//clear the form filter
function reset_form_filter(){
	$("#mf_form_list > li").hide();
	$("#mf_pagination").show();
	
	if($("#mf_pagination > li.current_page").length > 0){
		$($("#mf_pagination > li.current_page").data('liform_list')).show();
	}else{
		$("#mf_form_list > li").show();
	}

	$("#mf_form_list h3").unhighlight();
	$("ul.form_tag_list li").unhighlight();
	
	$("#filtered_result_box").fadeOut();
	$("#filtered_result_none").hide();
	
	$("#result_set_show_more").hide();
}


$(function(){
    
	/***************************************************************************************************************/	
	/* 1. Attach events to Form Title															   				   */
	/***************************************************************************************************************/
	
	//expand the form list when being clicked
	$(".middle_form_bar > h3").click(function(){
		var selected_form_li_id = $(this).parent().parent().attr('id');
		
		//show or hide all the options
		$("#" + selected_form_li_id + " .form_option").slideToggle('medium');
		
		//once all options has been shown/hide, toggle the parent class
		$("#" + selected_form_li_id + " .form_option").promise().done(function() {
			$(this).parent().toggleClass('form_selected');
		});

	});
	
	
	/***************************************************************************************************************/	
	/* 2. Attach events to 'Disable' link														   				   */
	/***************************************************************************************************************/
	
	//enable or disable the form
	$(".mf_link_disable a").click(function(){
		var selected_form_li_id = $(this).parent().parent().attr('id');
		
		var temp_form_id = selected_form_li_id.split('_');
		var current_form_id = temp_form_id[1];
		
		
		var current_action = '';
		
		if($(this).text() == 'Disable'){
			current_action = 'disable';
		}else if($(this).text() == 'Enable'){
			current_action = 'enable';
		}
		
		if(current_action == 'disable'){
			selected_form_id = current_form_id;
			$("#dialog-disabled-message").dialog('open');
		}else if(current_action == 'enable'){
			//change the 'Delete' text
			$(this).text('Processing...');
			
			//display the loader image
			$(this).parent().css("position","relative");
			$(this).after('<img src="images/loader_small_grey.gif" style="position: absolute;margin-left: 12px" />');
			
			//do the ajax call to enable or disable the form
			$.ajax({
				   type: "POST",
				   async: true,
				   url: "toggle_form.php",
				   data: {
						  form_id: current_form_id,
						  action: current_action
						 },
				   cache: false,
				   global: true,
				   dataType: "json",
				   error: function(xhr,text_status,e){
					  //restore the links upon error
					  if(current_action == 'disable'){
						  current_action = 'Disable';
					  }else if(current_action == 'Enable'){
						  current_action = 'Enable';
					  }
					  $("#" + selected_form_li_id + " .mf_link_disable a").text(current_action);
					  $("#" + selected_form_li_id + " .mf_link_disable img").remove();
				   },
				   success: function(response_data){
					   
					   if(response_data.status == 'ok'){
						   if(response_data.action == 'disable'){
							   $("#liform_" + response_data.form_id).addClass('form_inactive');
							   $("#liform_" + response_data.form_id + " .mf_link_disable a").html('<span class="icon-play"></span>Enable');
							   $("#liform_" + response_data.form_id + " .mf_link_disable img").remove();
						   }else{
							   $("#liform_" + response_data.form_id).removeClass('form_inactive');
							   $("#liform_" + response_data.form_id + " .mf_link_disable a").html('<span class="icon-pause"></span>Disable');
							   $("#liform_" + response_data.form_id + " .mf_link_disable img").remove();
						   }
					   }else{
						   //unknown error, response json improperly formatted
						   //restore the links upon error
						   if(current_action == 'disable'){
							   current_action = 'Disable';
						   }else if(current_action == 'Enable'){
							   current_action = 'Enable';
						   }
						   
						   $("#" + selected_form_li_id + " .mf_link_disable a").text(current_action);
						   $("#" + selected_form_li_id + " .mf_link_disable img").remove();
					   }
					   
				   }
			}); //end of ajax call
		}
		return false;
	});

	//Dialog box to disable a form
	$("#dialog-disabled-message").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 490,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			//populate the current message
			var current_message = $("#liform_" + selected_form_id).data("form_disabled_message");
			console.log(current_message);
			if(current_message == "" || current_message == null){
				current_message = 'This form is currently inactive.';
			}
			$("#dialog-disabled-message-input").val(current_message);
		},
		buttons: [{
				text: 'Yes. Disable this form',
				id: 'dialog-disabled-message-btn-save',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					if($("#dialog-disabled-message-input").val() == ""){
						alert('Please enter a message!');
					}else{
						
						//disable the save changes button while processing
						$("#dialog-disabled-message-btn-save").prop("disabled",true);
						
						//display loader image
						$("#dialog-disabled-message-btn-cancel").hide();
						$("#dialog-disabled-message-btn-save").text('Processing...');
						$("#dialog-disabled-message-btn-save").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
						
						//do the ajax call to disable the form						
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "toggle_form.php",
							   data: {
									  form_id: selected_form_id,
									  action: 'disable',
									  disabled_message: $("#dialog-disabled-message-input").val()
									 },
							   cache: false,
							   global: true,
							   dataType: "json",
							   error: function(xhr,text_status,e){
								  alert('Error! Unable to process');
							   },
							   success: function(response_data){
								   
								   if(response_data.status == 'ok'){
									   
								   		//restore the buttons and close the dialog box
								   	   	$("#dialog-disabled-message-btn-save").prop("disabled",false);
								       	$("#dialog-disabled-message-btn-cancel").show();
									   	$("#dialog-disabled-message-btn-save").text('Yes. Disable this form');
									  	$("#dialog-disabled-message-btn-save").next().remove();

									   	$("#dialog-disabled-message").dialog('close');
								   	   
								   	   	//update the dom data
								   	   	$("#liform_" + selected_form_id).data("form_disabled_message",$("#dialog-disabled-message-input").val());

									   	if(response_data.action == 'disable'){
										   $("#liform_" + response_data.form_id).addClass('form_inactive');
										   $("#liform_" + response_data.form_id + " .mf_link_disable a").html('<span class="icon-play"></span>Enable');
										   $("#liform_" + response_data.form_id + " .mf_link_disable img").remove();
									   	}
									   
								   }
								   
							   }
						}); //end of ajax call
						
					}
				}
			},
			{
				text: 'Cancel',
				id: 'dialog-disabled-message-btn-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	
	/***************************************************************************************************************/	
	/* 3. Attach events to pagination buttons													   				   */
	/***************************************************************************************************************/
	
	$("#mf_pagination > li").click(function(){
		var display_list = $(this).data('liform_list');
		
		$("#mf_form_list > li").hide();
		$(display_list).show();
		
		$("#mf_pagination > li.current_page").removeClass('current_page');
		$(this).addClass('current_page');
	});
	
	
	/***************************************************************************************************************/	
	/* 4. Attach events to search input															   				   */
	/***************************************************************************************************************/
	
	//expand the search box
	$("#filter_form_input").bind('focusin click',function(){
		
		if($("#filter_form_input").val() == 'find form...'){
			$("#filter_form_input").val('');
	
			$("#mf_search_box,#filter_form_input").animate({'width': '+=165px'},{duration:200,queue:false});
			
			$("#mf_search_box,#filter_form_input").promise().done(function() {
				$("#mf_search_title,#mf_search_tag").slideDown('medium');
				
				$("#mf_search_title,#mf_search_tag").promise().done(function(){
					$("#mf_search_box").addClass('search_focused');
					$("#mf_search_box,#filter_form_input").removeAttr('style');
				});
			});	
		}
		
		//shrink all opened forms
		$('.form_selected .form_option').hide();
		$(".form_selected").removeClass('form_selected');
		
	});
	
	//attach event to 'form title / form tags' tabs
	$("#mf_search_title").click(function(){
		$(this).addClass('mf_pane_selected');
		$("#mf_search_title a").html('&#8674; form title');
		
		$("#mf_search_tag a").html('form tags');
		$("#mf_search_tag").removeClass('mf_pane_selected');
		$("#filter_form_input").val('');
		
		//restore back the filter to the original condition
		reset_form_filter();
		
		$("#filter_form_input").focus();
		
		return false;
	});
	
	$("#mf_search_tag").click(function(){
		$(this).addClass('mf_pane_selected');
		$("#mf_search_tag a").html('&#8674; form tags')
		
		$("#mf_search_title a").html('form title');
		$("#mf_search_title").removeClass('mf_pane_selected');
		$("#filter_form_input").val('');
		
		//restore back the filter to the original condition
		reset_form_filter();
		
		$("#filter_form_input").focus();
		
		return false;
	});
	
	
	//filter the form when user type the search term
	$("#filter_form_input").keyup(function(){
		var search_term = $(this).val();
		var max_search_result = 10;
		
		
		if(search_term != ''){
			//first hide all form
			$("#mf_form_list > li").removeClass('result_set').hide();
			
			//hide pagination
			$("#mf_pagination").hide();
			
			if($("#mf_search_title").hasClass('mf_pane_selected')){ //search on form title
				var result_h3 = $("#mf_form_list h3:contains('"+ search_term + "')");
				
				result_h3.parent().parent().show().addClass('result_set');
				result_h3.unhighlight();
				result_h3.highlight(search_term);
				
				$("#filtered_result_box span").text(search_term);
				$("#filtered_result_box").fadeIn();
				
				$("#filtered_result_total").text('Found ' + result_h3.length + ' forms');
				
				if(result_h3.length == 0){
					$("#filtered_result_none").fadeIn();
				}else{
					$("#filtered_result_none").hide();
				}
				
				//if the result set exceed the limit, hide the rest and display "show more" button
				if(result_h3.length > max_search_result){
					$("#result_set_show_more").show();
					
					$(".result_set:gt("+ (max_search_result - 1) + ")").hide();
				}else{
					$("#result_set_show_more").hide();
				}
			}else{ //search on form tags
				var result_li = $("ul.form_tag_list li:contains('"+ search_term + "')");
				
				result_li.parent().parent().parent().parent().parent().show().addClass('result_set');
				result_li.unhighlight();
				result_li.highlight(search_term);
				
				$("#filtered_result_box span").text(search_term);
				$("#filtered_result_box").fadeIn();
				
				$("#filtered_result_total").text('Found ' + result_li.length + ' forms');
				
				if(result_li.length == 0){
					$("#filtered_result_none").fadeIn();
				}else{
					$("#filtered_result_none").hide();
				}
				
				//if the result set exceed the limit, hide the rest and display "show more" button
				if(result_li.length > max_search_result){
					$("#result_set_show_more").show();
					
					$(".result_set:gt("+ (max_search_result - 1) + ")").hide();
				}else{
					$("#result_set_show_more").hide();
				}
			}
			
		}else{
			//if the filter keyword is empty, restore back to the original condition
			reset_form_filter();
			
		}
		
	});
	
	$("#mf_filter_reset").click(function(){
		reset_form_filter();

		$("#mf_search_box").removeClass('search_focused');
		$("#mf_search_title,#mf_search_tag").hide();
		
		$("#filter_form_input").val('find form...');
		
		return false;
	});
	
	//attach event handler to "show more result" on filter result
	$("#result_set_show_more > a").click(function(){
		var show_more_increment = 20; //the number of more results being displayed each time the button being clicked
		
		var last_result_index = $(".result_set:visible").last().index('.result_set');
		var next_start_index = last_result_index + 1;
		var next_end_index   = next_start_index + show_more_increment;
		
		$(".result_set").slice(next_start_index,next_end_index).fadeIn();
		
		if(next_end_index >= $(".result_set").length){
			$("#result_set_show_more").hide();
		}
		
		return false;
	});
	
	/***************************************************************************************************************/	
	/* 5. Dialog box to enter a tag name														   				   */
	/***************************************************************************************************************/
	
	//Dialog box to assign tag names to form
	$("#dialog-enter-tagname").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Save Changes',
				id: 'dialog-enter-tagname-btn-save-changes',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					var form_id  = parseInt($("#dialog-enter-tagname").data('form_id'));
					
					if($("#dialog-enter-tagname-input").val() == ""){
						alert('Please enter a tag name!');
					}else{
						
						$(this).dialog('close');
						
						//display progress bar
						$("#liform_" + form_id + " ul.form_tag_list").append("<li class=\"processing\"><img src='images/loader_small_grey.gif' /></li>");
						
						//do the ajax call to save the tags
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "save_tags.php",
							   data: {
										action: 'add',
										form_id: form_id,
									  	tags: $("#dialog-enter-tagname-input").val()
									  },
							   cache: false,
							   global: false,
							   dataType: "json",
							   error: function(xhr,text_status,e){
									$("#liform_" + form_id + " ul.form_tag_list li.processing").remove();
									alert('Error! Unable to add tag names. Please try again.');	  
							   },
							   success: function(response_data){
									   
								   if(response_data.status == 'ok'){
									   $("#liform_" + response_data.form_id + " li.form_tag_list_icon").siblings().remove()
									   $("#liform_" + response_data.form_id + " ul.form_tag_list").append(response_data.tags_markup);
								   }else{
									   $("#liform_" + response_data.form_id + " ul.form_tag_list li.processing").remove();
									   alert('Error! Unable to add tag names. Please try again.');
								   }
									   
							   }
						});
						
					}
				}
			},
			{
				text: 'Cancel',
				id: 'dialog-enter-tagname-btn-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	//if the user submit the form by hitting the enter key, make sure to call the button_save_theme handler
	$("#dialog-enter-tagname-form").submit(function(){
		$("#dialog-enter-tagname-btn-save-changes").click();
		return false;
	});
	
	//attach event to add form tag
	$("ul.form_tag_list a.addtag").click(function(){
		var temp = $(this).attr('id').split('_');
		
		$("#dialog-enter-tagname").data('form_id',temp[1]);
		$("#dialog-enter-tagname-input").val('');
		$("#dialog-enter-tagname").dialog('open');
		
		return false;
	});
	
	//delegate onclick event to delete tag link
	$('#mf_form_list').delegate('a.removetag', 'click', function(e) {
		
		var selected_list = $(this).parent().parent().closest('li').attr('id');
		
		var temp = selected_list.split('_');
		var form_id = parseInt(temp[1]);
		
		var selected_tagname = $(this).parent().text();
		var parent_list = $(this).parent();
		
		//do the ajax call to delete the tag
		if($(this).find('img').attr("src") != "images/loader_green_16.png"){
			$(this).find('img').attr("src","images/loader_green_16.png");
			
			//do the ajax call to save the tags
			$.ajax({
				   type: "POST",
				   async: true,
				   url: "save_tags.php",
				   data: {
							action: 'delete',
							form_id: form_id,
						  	tags: selected_tagname
						  },
				   cache: false,
				   global: false,
				   dataType: "json",
				   error: function(xhr,text_status,e){
					    parent_list.find('img').attr("src","images/icons/53.png");
						alert('Error! Unable to delete tag name. Please try again.');	  
				   },
				   success: function(response_data){
						   
					   if(response_data.status == 'ok'){
						   parent_list.fadeOut(function(){$(this).remove()});
					   }else{
						   parent_list.find('img').attr("src","images/icons/53.png");
						   alert('Error! Unable to delete tag name. Please try again.');
					   }
						   
				   }
			});
		}
		
		
		return false;
    });
	
	//initialize the tagname input box with the existing tags
	$("#dialog-enter-tagname-input").autocomplete({
	         source: $("#dialog-enter-tagname-input").data('available_tags')
	});
	
	/***************************************************************************************************************/	
	/* 6. Attach events to 'Duplicate' link														   				   */
	/***************************************************************************************************************/
	
	$(".mf_link_duplicate a").click(function(){
		var selected_form_li_id = $(this).parent().parent().attr('id');
		
		var temp_form_id = selected_form_li_id.split('_');
		var current_form_id = temp_form_id[1];
		
		if($(this).text() == 'Duplicating...'){
			return false; //prevent the user from clicking multiple times
		}
		
		//change the 'Duplicate' text
		$(this).text('Duplicating...');
			
		//display the loader image
		$(this).parent().css("position","relative");
		$(this).before('<img src="images/loader_small_grey.gif" style="position: absolute;margin-left: -28px" />');
			
		//do the ajax call to duplicate the form
		$.ajax({
			   type: "POST",
			   async: true,
			   url: "duplicate_form.php",
			   data: {
					  form_id: current_form_id
					 },
			   cache: false,
			   global: true,
			   dataType: "json",
			   error: function(xhr,text_status,e){
				  //restore the links upon error
				  $("#" + selected_form_li_id + " .mf_link_duplicate a").text('Duplicate');
				  $("#" + selected_form_li_id + " .mf_link_duplicate img").remove();
				  alert('Error! Unable to duplicate. Please try again.');
				  
			   },
			   success: function(response_data){
					   
				   if(response_data.status == 'ok'){
					   window.location.replace('manage_forms.php?id=' + response_data.form_id + '&hl=true');
				   }else{
					   //unknown error, response json improperly formatted
					   //restore the links upon error
					   $("#" + selected_form_li_id + " .mf_link_duplicate a").text('Duplicate');
					   $("#" + selected_form_li_id + " .mf_link_duplicate img").remove();
					   alert('Error! Unable to duplicate. Please try again.');
				   }
					   
			   }
			}); //end of ajax call
		
		return false;
	});
	
	/***************************************************************************************************************/	
	/* 7. Highlight particular form if the variable exist														   */
	/***************************************************************************************************************/
	
	//this is being used to highlight a newly created form, as a result of a duplicate action
	if(selected_form_id_highlight > 0){
		$("#liform_" + selected_form_id_highlight + " div.middle_form_bar").hide().fadeIn();
	}
	
	/***************************************************************************************************************/	
	/* 8. Attach events to 'Delete' link														   				   */
	/***************************************************************************************************************/
	
	//dialog box to confirm deletion
	$("#dialog-confirm-form-delete").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-form-delete-ok").blur();
		},
		buttons: [{
				text: 'Yes. Delete this form',
				id: 'btn-form-delete-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					var form_id  = parseInt($("#dialog-confirm-form-delete").data('form_id'));
					
					$("#dropui_theme_options div.dropui-content").attr("style","");
					
					//disable the delete button while processing
					$("#btn-form-delete-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-form-delete-cancel").hide();
					$("#btn-form-delete-ok").text('Deleting...');
					$("#btn-form-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					//do the ajax call to delete the form
					
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "delete_form.php",
						   data: {
								  	form_id: form_id
								  },
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
								   //error, display the generic error message		  
						   },
						   success: function(response_data){
									   
							   if(response_data.status == 'ok'){
								   //redirect to form manager
								   window.location.replace('manage_forms.php');
							   }	  
									   
						   }
					});
					
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-form-delete-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	//open the dialog when the delete link clicked
	$(".mf_link_delete a").click(function(){
		var parent_li = $(this).parent().parent();
		var temp = parent_li.attr('id').split('_');
		var form_id = parseInt(temp[1]);
		
		$("#confirm_form_delete_name").text(parent_li.find('h3').text());
		$("#dialog-confirm-form-delete").data('form_id',form_id);
		$("#dialog-confirm-form-delete").dialog('open');
		
		return false;
	});
	
	/***************************************************************************************************************/	
	/* 9. Attach events to 'Theme' link														   				   */
	/***************************************************************************************************************/
	
	//dialog box to change a theme 
	$("#dialog-change-theme").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Save Changes',
				id: 'btn-change-theme-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					var form_id  = parseInt($("#dialog-change-theme").data('form_id'));
					
					
					//disable the delete button while processing
					$("#btn-change-theme-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-change-theme-cancel").hide();
					$("#btn-change-theme-ok").text('Applying Theme...');
					$("#btn-change-theme-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					//do the ajax call to delete the form
					
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "change_theme.php",
						   data: {
								  	form_id: form_id,
								  	theme_id: $("#dialog-change-theme-input").val()
								  },
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
								   //error, display the generic error message	
								  $("#btn-change-theme-cancel").show();
								  $("#btn-change-theme-ok").text('Save Changes');
							      $("#btn-change-theme-ok").next().remove();
							      $("#btn-change-theme-ok").prop("disabled",false);
							      
							      alert('Error! Unable to apply the theme. Please try again.');
						   },
						   success: function(response_data){
							   
							   $("#btn-change-theme-cancel").show();
							   $("#btn-change-theme-ok").text('Save Changes');
							   $("#btn-change-theme-ok").next().remove();
							   $("#btn-change-theme-ok").prop("disabled",false);
							  
							   if(response_data.status == 'ok'){
								   $("#liform_" + form_id).data('theme_id',$("#dialog-change-theme-input").val());
								   $("#dialog-change-theme").dialog('close');
							   }else{
								   alert('Error! Unable to apply the theme. Please try again.');
							   }
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-change-theme-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	//open the dialog when the change theme link clicked
	$(".mf_link_theme").click(function(){
		
		var parent_li = $(this).parent().parent();
		var temp = parent_li.attr('id').split('_');
		var form_id = parseInt(temp[1]);
		
		$("#dialog-change-theme").data('form_id',form_id);
		
		//set the value of the theme dropdown to the current active theme for this form
		$("#dialog-change-theme-input").val(parent_li.data('theme_id'));
		$("#dialog-change-theme").dialog('open');
		
		return false;
	});
	
	//if the user select "create new theme" on the theme selection dropdown
	$('#dialog-change-theme-input').bind('change', function() {
		if($(this).val() == "new"){
			//redirect to theme editor
			window.location.replace('edit_theme.php');
		}
	});
	
});
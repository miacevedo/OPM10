$(function(){

	//Initialize sortable event to the widget list
	$("#widget_list_sortable").sortable({ 
    										axis:'y' , 
    										opacity: 0.7,
    										placeholder: 'ui-state-highlight',
    										scrollSensitivity: 100, 
    										scrollSpeed: 40
    									});
	$("#widget_list_sortable").disableSelection();

	//attach event to the 'Sort Widgets' dropdown
	$("#sort_widget_link").click(function(){
		$("#mr_report_list,#mr_report_shared").hide();
		$("#widget_list_sortable").slideDown(function(){
			$("#report_sort_pane_apply").show();
		});

		return false;
	});

	//attach event to the 'Sort Widgets' cancel link
	$("#report_sort_pane_cancel").click(function(){
		$("#report_sort_pane_apply").hide();
		$("#widget_list_sortable").slideUp(function(){
			$("#mr_report_list,#mr_report_shared").show();
		});

		return false;
	});
	
	//attach event to the 'Sort Widgets' save changes button
	$("#mr_report_sort_pane_submit").click(function(){

		$(this).val("Saving...");
		$(this).prop("disabled",true);
		
		$("#cancel_report_sort_pane_span").hide();
		$(this).after("<span id='field_select_loader'><img src='images/loader_small_grey.gif' style='vertical-align: middle;margin-left: 5px'/></span>");

		var form_id 		 = $("#widget_list_sortable").data("formid");
		var widget_positions = $("#widget_list_sortable").sortable('serialize',{key:'widget_pos[]'});

		//send to backend using ajax call
		$.ajax({
			   type: "POST",
			   async: true,
			   url: "save_widgets_position.php",
			   data: {form_id: form_id,
					  widget_pos: widget_positions
					  },
			   cache: false,
			   global: false,
			   dataType: "json",
			   error: function(xhr,text_status,e){
					   //error, display the generic error message		  
			   },
			   success: function(response_data){
					   
				   if(response_data.status == 'ok'){
					   window.location.replace('manage_report.php?id=' + response_data.form_id);
				   }	  
					   
			   }
		});
		
	});

	//attach evenet to the Delete widget icons
	$("#mr_report_list").delegate('a.delete_icon', 'click', function(e) {
		var temps 	 = $(this).attr("id").split('_');
		var chart_id = temps[1];

		$("#dialog-delete-widget").data("chart_id",chart_id);
		$("#dialog-delete-widget").dialog('open');
		
		return false;
	});

	//attach event to the Share This Report link
	$("#share_report_link").click(function(){
		$("#dialog-share-report").dialog('open');
		
		return false;
	});

	//attach event to the Unshare Report link
	$("#unshare_report_link").click(function(){
		$("#dialog-unshare-report").dialog('open');
		
		return false;
	});

	//Confirmation message for deleting a widget.
	$("#dialog-delete-widget").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		resizable: false,
		draggable: false,
		position: ['center',150],
		open: function(){
			$("#btn-widget-delete-ok").blur();
		},
		buttons: [{
					text: 'Yes. Delete this widget',
					id: 'btn-widget-delete-ok',
					'class': 'bb_button bb_small bb_green',
					click: function() {	
						//disable the delete button while processing
						$("#btn-widget-delete-ok").prop("disabled",true);
							
						//display loader image
						$("#btn-widget-delete-cancel").hide();
						$("#btn-widget-delete-ok").text('Deleting...');
						$("#btn-widget-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
						
						var chart_id = $("#dialog-delete-widget").data("chart_id");
						var form_id  = $("#widget_list_sortable").data("formid");
						
						//do the ajax call to delete the widget
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "delete_widget.php",
							   data: {
									  	form_id: form_id,
									  	chart_id: chart_id
									  },
							   cache: false,
							   global: false,
							   dataType: "json",
							   error: function(xhr,text_status,e){
									   //error, display the generic error message		  
							   },
							   success: function(response_data){
								   //restore back the buttons
								   $("#btn-widget-delete-ok").prop("disabled",false);

								   $("#btn-widget-delete-cancel").show();
								   $("#btn-widget-delete-ok").text('Yes. Delete this widget');
								   $(".small_loader_box").remove();

								   $("#dialog-delete-widget").dialog('close');

								   if(response_data.status == 'ok'){
									  	$("#li_" + response_data.chart_id).fadeOut(function(){
									  		$(this).remove();
									  	});
									  	$("#widget_" + response_data.chart_id).remove();

									  	 //refresh the sortable cache
					   					$("#widget_list_sortable").sortable("refreshPositions");
								   }	  
										   
							   }
						});
						
					}
				},
				{
					text: 'Cancel',
					id: 'btn-widget-delete-cancel',
					'class': 'btn_secondary_action',
					click: function() {
						$(this).dialog('close');
					}
				}]
	});
	//end dialog box

	//Confirmation message for sharing the report.
	$("#dialog-share-report").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		resizable: false,
		draggable: false,
		position: ['center',150],
		open: function(){
			$("#btn-share-report-ok").blur();
		},
		buttons: [{
					text: 'Yes. Share this report',
					id: 'btn-share-report-ok',
					'class': 'bb_button bb_small bb_green',
					click: function() {	
						//disable the share button while processing
						$("#btn-share-report-ok").prop("disabled",true);
							
						//display loader image
						$("#btn-share-report-cancel").hide();
						$("#btn-share-report-ok").text('Sharing...');
						$("#btn-share-report-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
						
						var form_id  = $("#widget_list_sortable").data("formid");
						
						//do the ajax call to share the report
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "share_report.php",
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
								   //restore back the buttons
								   $("#btn-share-report-ok").prop("disabled",false);

								   $("#btn-share-report-cancel").show();
								   $("#btn-share-report-ok").text('Yes. Share this report');
								   $(".small_loader_box").remove();

								   $("#dialog-share-report").dialog('close');

								   if(response_data.status == 'ok'){
									  	$("#li_share_report").hide();
									  	$("#li_unshare_report").show();

									  	$("#mr_report_shared_span").html(response_data.report_link);
									  	$("#mr_report_shared").slideDown();
								   }	  
										   
							   }
						});
						
					}
				},
				{
					text: 'Cancel',
					id: 'btn-share-report-cancel',
					'class': 'btn_secondary_action',
					click: function() {
						$(this).dialog('close');
					}
				}]
	});
	//end dialog box

	//Confirmation message for unsharing the report.
	$("#dialog-unshare-report").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		resizable: false,
		draggable: false,
		position: ['center',150],
		open: function(){
			$("#btn-unshare-report-ok").blur();
		},
		buttons: [{
					text: 'Yes. Unshare this report',
					id: 'btn-share-report-ok',
					'class': 'bb_button bb_small bb_green',
					click: function() {	
						//disable the share button while processing
						$("#btn-unshare-report-ok").prop("disabled",true);
							
						//display loader image
						$("#btn-unshare-report-cancel").hide();
						$("#btn-unshare-report-ok").text('Removing...');
						$("#btn-unshare-report-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
						
						var form_id  = $("#widget_list_sortable").data("formid");
						
						//do the ajax call to share the report
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "unshare_report.php",
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
								   //restore back the buttons
								   $("#btn-unshare-report-ok").prop("disabled",false);

								   $("#btn-unshare-report-cancel").show();
								   $("#btn-unshare-report-ok").text('Yes. Unshare this report');
								   $(".small_loader_box").remove();

								   $("#dialog-unshare-report").dialog('close');

								   if(response_data.status == 'ok'){
									  	$("#li_share_report").show();
									  	$("#li_unshare_report").hide();

									  	$("#mr_report_shared").slideUp();
								   }	  
										   
							   }
						});
						
					}
				},
				{
					text: 'Cancel',
					id: 'btn-unshare-report-cancel',
					'class': 'btn_secondary_action',
					click: function() {
						$(this).dialog('close');
					}
				}]
	});
	//end dialog box

});
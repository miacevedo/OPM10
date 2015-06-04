$(function(){
    
	//dialog box to confirm entry deletion
	$("#dialog-confirm-entry-delete").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-confirm-entry-delete-ok").blur();
		},
		buttons: [{
				text: 'Yes. Delete this entry',
				id: 'btn-confirm-entry-delete-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					//disable the delete button while processing
					$("#btn-confirm-entry-delete-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-confirm-entry-delete-cancel").hide();
					$("#btn-confirm-entry-delete-ok").text('Deleting...');
					$("#btn-confirm-entry-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					var form_id  = $("#ve_details").data("formid");
					var entry_id = $("#ve_details").data("entryid"); 
					var selected_entry = [{name: "entry_" + entry_id, value: "1"}];

					//do the ajax call to delete the entries
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "delete_entries.php",
						   data: {
								  	form_id: form_id,
								  	origin: 'view_entry',
								  	incomplete_entries: $("#ve_details").data("incomplete"),
								  	selected_entries: selected_entry
								  },
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
								   //error, display the generic error message		  
						   },
						   success: function(response_data){
									   
							   if(response_data.status == 'ok'){
								   //redirect to entries page again
								   if(response_data.entry_id != '0' && response_data.entry_id != ''){
								   		window.location.replace('view_entry.php?form_id=' + response_data.form_id + '&entry_id=' + response_data.entry_id);
								   }else{
								   		window.location.replace('manage_entries.php?id=' + response_data.form_id);
								   }
								  
							   }	  
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-confirm-entry-delete-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	//open the deletion dialog when the delete entry link clicked
	$("#ve_action_delete").click(function(){	
		$("#dialog-confirm-entry-delete").dialog('open');
		return false;
	});

	//dialog box to email the entry
	$("#dialog-email-entry").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Email Entry',
				id: 'dialog-email-entry-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {

					if($("#dialog-email-entry-input").val() == ""){
						alert('Please enter the email address!');
					}else{
						
						var form_id  = $("#ve_details").data("formid");
						var entry_id = $("#ve_details").data("entryid"); 

						//disable the email entry button while processing
						$("#dialog-email-entry-btn-ok").prop("disabled",true);
						
						//display loader image
						$("#dialog-email-entry-btn-cancel").hide();
						$("#dialog-email-entry-btn-ok").text('Sending...');
						$("#dialog-email-entry-btn-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
						
						//do the ajax call to send the entry
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "email_entry.php",
							   data: {
									  	form_id: form_id,
									  	entry_id: entry_id,
									  	target_email: $("#dialog-email-entry-input").val()
									  },
							   cache: false,
							   global: false,
							   dataType: "json",
							   error: function(xhr,text_status,e){
							   		//restore the buttons on the dialog
									$("#dialog-email-entry").dialog('close');
									$("#dialog-email-entry-btn-ok").prop("disabled",false);
									$("#dialog-email-entry-btn-cancel").show();
									$("#dialog-email-entry-btn-ok").text('Email Entry');
									$("#dialog-email-entry-btn-ok").next().remove();
									$("#dialog-email-entry-input").val('');
									
									alert('Error! Unable to send entry. \nError message: ' + xhr.responseText); 
							   },
							   success: function(response_data){
									
									//restore the buttons on the dialog
									$("#dialog-email-entry").dialog('close');
									$("#dialog-email-entry-btn-ok").prop("disabled",false);
									$("#dialog-email-entry-btn-cancel").show();
									$("#dialog-email-entry-btn-ok").text('Email Entry');
									$("#dialog-email-entry-btn-ok").next().remove();
									$("#dialog-email-entry-input").val('');
									   	   
								   if(response_data.status == 'ok'){
									   //display the confirmation message
									   $("#dialog-entry-sent").dialog('open');
								   } 
									   
							   }
						});
					}
				}
			},
			{
				text: 'Cancel',
				id: 'dialog-email-entry-btn-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//open the email entry dialog when the email entry link clicked
	$("#ve_action_email").click(function(){	
		$("#dialog-email-entry").dialog('open');
		return false;
	});

	//if the user submit the form by hitting the enter key, make sure to call the button-email-entry handler
	$("#dialog-email-entry-form").submit(function(){
		$("#dialog-email-entry-btn-ok").click();
		return false;
	});

	//Dialog to display entry being sent successfully
	$("#dialog-entry-sent").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'OK',
				id: 'dialog-entry-sent-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//attach event to "change payment status" link
	$("#payment_status_change_link").click(function(){	
		$("#payment_status_static").hide();
		$("#payment_status_form").show();

		return false;
	});

	//attach event to "cancel" link on payment status
	$("#payment_status_cancel_link").click(function(){	
		$("#payment_status_form").hide();
		$("#payment_status_static").show();

		return false;
	});

	//attach event to "save" link on payment status
	$("#payment_status_save_link").click(function(){	
		
		$("#payment_status_dropdown").prop("disabled",true);
		$("#payment_status_save_cancel").hide();
		$("#payment_status_loader").show();

		var form_id  = $("#ve_details").data("formid");
		var entry_id = $("#ve_details").data("entryid");

		//do the ajax call to send the entry
		$.ajax({
			   type: "POST",
			   async: true,
			   url: "change_payment_status.php",
			   data: {
					  	form_id: form_id,
					  	entry_id: entry_id,
					  	payment_status: $("#payment_status_dropdown").val()
					  },
			   cache: false,
			   global: false,
			   dataType: "json",
			   error: function(xhr,text_status,e){
			   		//restore the links to original and display alert
					$("#payment_status_dropdown").prop("disabled",false);
					$("#payment_status_save_cancel").show();
					$("#payment_status_loader").hide();

					alert('Error! Unable to change status. \nError message: ' + xhr.responseText); 
			   },
			   success: function(response_data){
					//restore the link and update the payment status
					$("#payment_status_dropdown").prop("disabled",false);
					$("#payment_status_save_cancel").show();
					$("#payment_status_loader").hide();

					if(response_data.status == 'ok'){
						$(".payment_status").removeClass('paid').text(response_data.payment_status.toUpperCase());	

						if(response_data.payment_status == 'paid'){
							$(".payment_status").addClass('paid');
						}

						$("#payment_status_form").hide();
						$("#payment_status_static").show();
					}else{
						alert('Error! Unable to change status. \nError message: ' + xhr.responseText); 
					}    
			   }
		});

		return false;
	});
	
});
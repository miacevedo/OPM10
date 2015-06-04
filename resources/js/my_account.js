$(function(){
    
	/***************************************************************************************************************/	
	/* 1. Load Tooltips															   				   				   */
	/***************************************************************************************************************/
	
	//we're using jquery tools for the tooltip	
	$(".helpmsg").tooltip({
		
		// place tooltip on the bottom
		position: "bottom center",
		
		// a little tweaking of the position
		offset: [10, 20],
		
		// use the built-in fadeIn/fadeOut effect
		effect: "fade",
		
		// custom opacity setting
		opacity: 0.8,
		
		events: {
			def: 'click,mouseout'
		}
		
	});
	
	
	/***************************************************************************************************************/	
	/* 2. Attach event to 'Save Settings' button																   */
	/***************************************************************************************************************/
	$("#button_save_main_settings").click(function(){
		
		if($("#button_save_main_settings").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_main_settings").prop("disabled",true);
				$("#button_save_main_settings").text('Saving...');
				$("#button_save_main_settings").after("<img style=\"margin-left: 10px\" src='images/loader_small_grey.gif' />");
				
				$("#ms_form").submit();
		}
		
		
		return false;
	});

	/***************************************************************************************************************/	
	/* 3. Dialog Box for change password																		   */
	/***************************************************************************************************************/
	
	$("#dialog-change-password").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center', 150],
		draggable: false,
		resizable: false,
		buttons: [{
			text: 'Save Password',
			id: 'dialog-change-password-btn-save-changes',
			'class': 'bb_button bb_small bb_green',
			click: function() {
				var password_1 = $.trim($("#dialog-change-password-input1").val());
				var password_2 = $.trim($("#dialog-change-password-input2").val());
				var current_user_id = $("#ms_box_account").data("userid");

				if(password_1 == "" || password_2 == ""){
					alert('Please enter both password fields!');
				}else if(password_1 != password_2){
					alert("Please enter the same password for both fields!");
				}else{
					//disable the save changes button while processing
					$("#dialog-change-password-btn-save-changes").prop("disabled",true);
						
					//display loader image
					$("#dialog-change-password-btn-cancel").hide();
					$("#dialog-change-password-btn-save-changes").text('Saving...');
					$("#dialog-change-password-btn-save-changes").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");

					//do the ajax call to change the password
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "change_password.php",
						   data: {
								  	np: password_1,
								  	user_id: current_user_id
								  },
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
							   //error, display the generic error message
							   alert('Unable to save the password!');
							   $(this).dialog('close');	  
						   },
						   success: function(response_data){	   
							   //restore the buttons on the dialog
								$("#dialog-change-password").dialog('close');
								$("#dialog-change-password-btn-save-changes").prop("disabled",false);
								$("#dialog-change-password-btn-cancel").show();
								$("#dialog-change-password-btn-save-changes").text('Save Password');
								$("#dialog-change-password-btn-save-changes").next().remove();
								$("#dialog-change-password-input1").val('');
								$("#dialog-change-password-input2").val('');
									   	   
								if(response_data.status == 'ok'){
									//display the confirmation message
									$("#dialog-password-changed").dialog('open');
								} 
						   }
					});
				}
			}
		},
		{
			text: 'Cancel',
			id: 'dialog-change-password-btn-cancel',
			'class': 'btn_secondary_action',
			click: function() {
				$(this).dialog('close');
			}
		}]

	});

	//Dialog to display password has been changed successfully
	$("#dialog-password-changed").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'OK',
				id: 'dialog-password-changed-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	$("#ms_change_password").click(function(){
		$("#dialog-change-password").dialog('open');
		return false;
	});

	
	
});
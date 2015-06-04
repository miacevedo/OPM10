(function($){
  $.fn.outerHTML = function() {
    var el = this[0];
    return !el ? null : el.outerHTML || $('<div />').append(el).html();
  }
})(jQuery);

function select_date(dates){

	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1] + '_' + temp[2];

	var selected_date = month + '/' + day + '/' + year;

	$("#conditionkeyword_" + li_id).val(selected_date);
	$("#lifieldrule_" + li_id).data('rule_condition').keyword = selected_date;
}

function select_date_page(dates){

	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1] + '_' + temp[2];

	var selected_date = month + '/' + day + '/' + year;

	$("#conditionkeyword_" + li_id).val(selected_date);
	$("#lipagerule_" + li_id).data('rule_condition').keyword = selected_date;
}

function select_date_email(dates){

	var month = dates[0].getMonth() + 1;
	var day   = dates[0].getDate();
	var year  = dates[0].getFullYear();
	
	var temp = $(this).attr("id").split("_");
	var li_id = temp[1] + '_' + temp[2];

	var selected_date = month + '/' + day + '/' + year;

	$("#conditionkeyword_" + li_id).val(selected_date);
	$("#liemailrule_" + li_id).data('rule_condition').keyword = selected_date;
}

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
	/* 2. Initialize Dialog Boxes												   				   				   */
	/***************************************************************************************************************/

	//Generic warning dialog to be used everywhere
	$("#dialog-warning").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 600,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$(this).next().find('button').blur()
		},
		buttons: [{
			text: 'OK',
			'class': 'bb_button bb_small bb_green',
			click: function() {
				$(this).dialog('close');
			}
		}]
	});

	/***************************************************************************************************************/	
	/* 3. 'Show / Hide Fields' pane												   				   				   */
	/***************************************************************************************************************/
	
	//attach event to 'enable show/hide field rules' checkbox
	$("#logic_field_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ls_box_field_rules .ls_box_content").slideDown();
			$(".logic_settings").data('logic_status').logic_field_enable = 1;
		}else{
			$("#ls_box_field_rules .ls_box_content").slideUp();
			$(".logic_settings").data('logic_status').logic_field_enable = 0;
		}
	});

	//attach event to 'add a field to show/hide' dropdown
	$('#ls_select_field_rule').bind('change', function() {
		
		if($(this).val() == ''){
			return true;
		}
		
		var element_id = parseInt($(this).val());

		//build the markup
		var li_markup = '';
		var condition_fieldname_markup = '';

		condition_fieldname_markup = $("#ls_fields_lookup").clone(false).attr("id","conditionfield_" + element_id + "_1").attr("name","conditionfield_" + element_id + "_1").show().outerHTML();

		li_markup 	+= 	'<li id="lifieldrule_'+ element_id +'" style="display: none">' +
							'<table width="100%" cellspacing="0">' +
							  '<thead>' +
							    '<tr>' +
							      '<td>' + 
							         '<strong>' + $(this).find('option:selected').text() + '</strong><a class="delete_lifieldrule" id="deletelifieldrule_'+ element_id +'" href="#"><img src="images/icons/52_blue_16.png"></a>' + 
							      '</td>' +
							    '</tr>' +
							  '</thead>' +
							  '<tbody>' +
							    '<tr>' +
							      '<td>' +
							      	'<h6>' +
							      		'<img src="images/icons/arrow_right_blue.png" style="vertical-align: top" /><select style="margin-left: 5px;margin-right: 5px" name="fieldruleshowhide_'+ element_id +'" id="fieldruleshowhide_' + element_id + '" class="element select rule_show_hide">' + 
											'<option value="show">Show</option>' +
											'<option value="hide">Hide</option>' +
										'</select> this field if ' + 
										'<select style="margin-left: 5px;margin-right: 5px" name="fieldruleallany_' + element_id + '" id="fieldruleallany_' + element_id + '" class="element select rule_all_any">' + 
											'<option value="all">all</option>' +
											'<option value="any">any</option>' +
										'</select>' + 
										' of the following conditions match:' +
									'</h6>' +
							      	'<ul class="ls_field_rules_conditions">' +
							      		'<li id="lifieldrule_' + element_id + '_1"> ' +
							      			 condition_fieldname_markup + ' ' +
											'<select name="conditiontext_'+ element_id +'_1" id="conditiontext_'+ element_id +'_1" class="element select condition_text" style="width: 120px;display: none">' +
												'<option value="is">Is</option>' +
												'<option value="is_not">Is Not</option>' +
												'<option value="begins_with">Begins with</option>' +
												'<option value="ends_with">Ends with</option>' +
												'<option value="contains">Contains</option>' +
												'<option value="not_contain">Does not contain</option>' +
											'</select>' + ' ' +
											'<select id="conditionnumber_'+ element_id +'_1" name="conditionnumber_' + element_id + '_1" style="width: 120px;display: none" class="element select condition_number">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="less_than">Less than</option>' + 
												'<option value="greater_than">Greater than</option>' + 
											'</select>' + ' ' +
											'<select id="conditiondate_'+ element_id +'_1" name="conditiondate_' + element_id + '_1" style="width: 120px;display: none" class="element select condition_date">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="is_before">Is Before</option>' + 
												'<option value="is_after">Is After</option>' + 
											'</select>' + ' ' + 
											'<select id="conditioncheckbox_'+ element_id +'_1" name="conditioncheckbox_' + element_id + '_1" style="width: 120px;display: none" class="element select condition_checkbox">' + 
												'<option value="is_one">Is Checked</option>' + 
												'<option value="is_zero">Is Empty</option>' + 
											'</select>' + ' ' + "\n" +
											'<span name="conditiontime_' + element_id + '_1" id="conditiontime_'+ element_id + '_1" class="condition_time" style="display: none">' + 
												'<input name="conditiontimehour_' + element_id + '_1" id="conditiontimehour_' + element_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="HH"> : ' + 
												'<input name="conditiontimeminute_' + element_id + '_1" id="conditiontimeminute_' + element_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="MM">  ' + 
												'<span class="conditiontime_second" style="display:none"> : <input name="conditiontimesecond_' + element_id + '_1" id="conditiontimesecond_' + element_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="SS"> </span>' + 
												'<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_' + element_id + '_1" id="conditiontimeampm_' + element_id + '_1" style="display:none">' + 
													'<option selected="selected" value="AM">AM</option>' + 
													'<option value="PM">PM</option>' + 
												'</select>' + 
											'</span>' + 
											'<input type="text" class="element text condition_keyword" value="" id="conditionkeyword_'+ element_id +'_1" name="conditionkeyword_'+ element_id +'_1" style="display: none"> ' + "\n" +
											'<a href="#" id="deletecondition_' + element_id + '_1" name="deletecondition_'+ element_id +'_1" class="a_delete_condition"><img src="images/icons/51_blue_16.png" /></a>' + "\n" +
							      		'</li>' +
							      		'<li class="ls_add_condition">' +
											'<a href="#" id="addcondition_'+ element_id +'" class="a_add_condition"><img src="images/icons/49_blue_16.png" /></a>' +
										'</li>' +
							      	'</ul>' +
							      '</td>' +
							    '</tr>' +
							  '</tbody>' +
							'</table>' +
						'</li>';

		//append the rule markup
		$("#ls_field_rules_group").prepend(li_markup);
		$("#lifieldrule_" + element_id).hide();

		//remove the current element from the list of condition
		$("#conditionfield_" + element_id + "_1 option[value=element_"+ element_id +"]").remove();
		$("#conditionfield_" + element_id + "_1 option[value^=element_"+ element_id +"_]").remove(); //remove childs element (for checkbox)

		//diplay the condition operator, depends on the first field on the field list
		var first_field_element_name = $("#conditionfield_" + element_id + "_1").eq(0).val();
		var first_field_element_type = $("#ls_fields_lookup").data(first_field_element_name);
		var default_condition = 'is';

		if(first_field_element_type == 'money' || first_field_element_type == 'number'){
			$("#conditionnumber_" + element_id + "_1").show();
			$("#conditionkeyword_" + element_id + "_1").show();
		}else if(first_field_element_type == 'date' || first_field_element_type == 'europe_date'){
			$("#conditiondate_" + element_id + "_1").show();
			$("#conditionkeyword_" + element_id + "_1").show();

			$("#lifieldrule_" + element_id + "_1").addClass("condition_date");
		}else if(first_field_element_type == 'time' || first_field_element_type == 'time_showsecond' || first_field_element_type == 'time_24hour' || first_field_element_type == 'time_showsecond24hour'){
			$("#conditiondate_" + element_id + "_1").show();
			$("#conditiontime_" + element_id + "_1").show();
			
			if(first_field_element_type == 'time'){
				$("#conditiontimeampm_" + element_id + "_1").show();
			}else if(first_field_element_type == 'time_showsecond'){
				$("#conditiontimeampm_" + element_id + "_1").show();
				$("#conditiontimesecond_" + element_id + "_1").parent().show();
			}else if(first_field_element_type == 'time_showsecond24hour'){
				$("#conditiontimesecond_" + element_id + "_1").parent().show();
			}

		}else if(first_field_element_type == 'checkbox'){
			$("#conditioncheckbox_" + element_id + "_1").show();
			default_condition = 'is_one'
		}else{
			$("#conditiontext_" + element_id + "_1").show();
			$("#conditionkeyword_" + element_id + "_1").show();
		}

		//build the datepicker
		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_'+ element_id +'_1" id="datepicker_'+ element_id +'_1">' + "\n" +
							 	 ' <span style="display:none"><img id="datepickimg_'+ element_id +'_1" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + element_id + '_1').after(new_datepicker_tag);

		$('#datepicker_' + element_id + '_1').datepick({ 
		   		onSelect: select_date,
		   		showTrigger: '#datepickimg_' + element_id + '_1'
		});

		$("#lifieldrule_" + element_id).slideDown();

		//attach dom data
		$("#lifieldrule_" + element_id).data('rule_properties',{"element_id": element_id,"rule_show_hide":"show","rule_all_any":"all"});
		$("#lifieldrule_" + element_id + "_1").data('rule_condition',{"target_element_id": element_id,"element_name": first_field_element_name, "condition": default_condition,"keyword":""});

		//remove the option from the dropdown
		$(this).find('option:selected').remove();
		
		if($("#ls_select_field_rule option").length == 1){
			$("#ls_select_field_rule option").text('No More Fields Available');
		}

	});
	
	//delegate change event to the show/hide dropdown
    $('#ls_box_field_rules').delegate('select.rule_show_hide', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#lifieldrule_" + temp[1]).data('rule_properties').rule_show_hide = $(this).val();
    });

    //delegate change event to the all/any dropdown
    $('#ls_box_field_rules').delegate('select.rule_all_any', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#lifieldrule_" + temp[1]).data('rule_properties').rule_all_any = $(this).val();
    });
	
	//delegate change event into condition field name dropdown
	$('#ls_box_field_rules').delegate('select.condition_fieldname', 'change', function(e) {
			
			var new_element_name = $(this).val();
			var new_element_type = $("#ls_fields_lookup").data(new_element_name);

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_checkbox,.condition_keyword').hide();
			$(this).parent().removeClass('condition_date');

			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'money' || new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'date' || new_element_type == 'europe_date'){
				$(this).parent().addClass('condition_date');
				$(this).parent().find('.condition_date,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'time' || new_element_type == 'time_showsecond' || new_element_type == 'time_24hour' || new_element_type == 'time_showsecond24hour'){
				$(this).parent().find('.condition_date,.condition_time').show();
				
				$(this).parent().find('.condition_time .conditiontime_second,.condition_time .conditiontime_ampm').hide();
				
				if(new_element_type == 'time'){
					$(this).parent().find('.condition_time .conditiontime_ampm').show();
				}else if(new_element_type == 'time_showsecond'){
					$(this).parent().find('.condition_time .conditiontime_ampm,.condition_time .conditiontime_second').show();
				}else if(new_element_type == 'time_showsecond24hour'){
					$(this).parent().find('.condition_time .conditiontime_second').show();
				}

				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'checkbox'){
				$(this).parent().find('.condition_checkbox').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_checkbox').val();
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('rule_condition').element_name = new_element_name;

    });
	
	//delegate change event to the condition type dropdown
    $('#ls_box_field_rules').delegate('select.condition_text,select.condition_number,select.condition_date,select.condition_checkbox', 'change', function(e) {
		$(this).parent().data('rule_condition').condition = $(this).val();
    });
	
	//delegate event to the condition keyword text
    $('#ls_box_field_rules').delegate('input.condition_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();	
    });

    //delegate event to the time condition inputs
    $('#ls_box_field_rules').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
		var temp = $(this).attr("id").split("_");

		var hour_value 	 = parseInt($("#conditiontimehour_" + temp[1] + "_" + temp[2]).val(),10);
		var minute_value = parseInt($("#conditiontimeminute_" + temp[1] + "_" + temp[2]).val(),10);
		var second_value = parseInt($("#conditiontimesecond_" + temp[1] + "_" + temp[2]).val(),10);
		
		var ampm_value 	 = $("#conditiontimeampm_" + temp[1] + "_" + temp[2]).val();

		if(isNaN(hour_value)){
			hour_value = '00';
		}

		if(isNaN(minute_value)){
			minute_value = '00';
		}
		
		if(isNaN(second_value)){
			second_value = '00';
		}

		$("#lifieldrule_" + temp[1] + "_" + temp[2]).data('rule_condition').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });
	
	//attach event to 'delete field rules' icon
	$('#ls_box_field_rules').delegate('a.delete_lifieldrule', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1];
		
		//restore field dropdown values
		$("#ls_select_field_rule").html($("#ls_select_field_rule_lookup").html());

		$("#lifieldrule_" + element_id).fadeOut(400,function(){
			$(this).remove();

			$("#ls_field_rules_group > li").each(function(){
				var temp_name = $(this).attr('id').split('_');
				var cur_element_id = temp_name[1];
				
				$("#ls_select_field_rule option[value="+ cur_element_id +"]").remove();			
			});
		});
		
		return false;
	});

	//attach click event to 'add rule condition' (+) icon
	$('#ls_box_field_rules').delegate('a.a_add_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1];

		var new_id = $("#lifieldrule_" + element_id + " ul > li:not('.ls_add_condition')").length + 1;
		var old_id = new_id - 1;

		//duplicate the last rule condition
		var last_rule_element = $("#lifieldrule_" + element_id + " ul > li:not('.ls_add_condition')").last();
		last_rule_element.clone(false).data('rule_condition',$.extend('{}',last_rule_element.data('rule_condition'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			
			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + temp[1] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + temp[1] + "_" + new_id);
			
		}).end().attr("id","lifieldrule_" + element_id + "_" + new_id).insertBefore("#lifieldrule_" + element_id + " li.ls_add_condition").hide().fadeIn();

		//copy the value of the dropdowns
		$("#conditionfield_" + element_id + "_" + new_id).val($("#conditionfield_" + element_id + "_" + old_id).val());
		$("#conditiontext_" + element_id + "_" + new_id).val($("#conditiontext_" + element_id + "_" + old_id).val());
		$("#conditionnumber_" + element_id + "_" + new_id).val($("#conditionnumber_" + element_id + "_" + old_id).val());
		$("#conditiondate_" + element_id + "_" + new_id).val($("#conditiondate_" + element_id + "_" + old_id).val());
		$("#conditioncheckbox_" + element_id + "_" + new_id).val($("#conditioncheckbox_" + element_id + "_" + old_id).val());
		
		//reset the condition keyword  
		$("#conditionkeyword_" + element_id + "_" + new_id).val('');
		$("#lifieldrule_" + element_id + "_" + new_id).data('rule_condition').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + element_id + '_' + new_id).next().next().remove();
		$('#datepicker_' + element_id + '_' + new_id).next().remove();
		$('#datepicker_' + element_id + '_' + new_id).remove();

		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_' + element_id + '_' + new_id +'" id="datepicker_' + element_id + '_' + new_id +'"> ' +
								 '<span style="display:none"> <img id="datepickimg_'+ element_id + '_' + new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + element_id + '_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + element_id + '_' + new_id).datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#datepickimg_' + element_id + '_' + new_id
		});

		return false;
	});

	//delegate click event to the 'delete rule condition' (-) icon
    $('#ls_box_field_rules').delegate('a.a_delete_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1];

		if($("#lifieldrule_" + element_id + " ul > li:not('.ls_add_condition')").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all condition. <br />You must have at least one condition.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });


    /***************************************************************************************************************/	
	/* 4. Attach event to 'Save Settings' button																   */
	/***************************************************************************************************************/
	$("#button_save_logics").click(function(){
		
		if($("#button_save_logics").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_logics").prop("disabled",true);
				$("#button_save_logics").text('Saving...');
				$("#button_save_logics").after("<div class='small_loader_box' style='float: right'><img src='images/loader_small_grey.gif' /></div>");
				
				//get field logic properties data
				var field_rule_properties_elements = $("#ls_field_rules_group > li");
				var field_rule_properties_data 	   = new Array();

				if(field_rule_properties_elements.length >= 1){
					field_rule_properties_elements.each(function(index){
						field_rule_properties_data[index] = $(this).data('rule_properties');
					});
				}

				var field_rule_condition_elements = $("#ls_field_rules_group ul.ls_field_rules_conditions > li:not('.ls_add_condition')");
				var field_rule_condition_data 	= new Array();

				if(field_rule_condition_elements.length >= 1){
					field_rule_condition_elements.each(function(index){
						field_rule_condition_data[index] = $(this).data('rule_condition');
					});
				}

				//get page logic properties data
				var page_rule_properties_elements = $("#ls_page_rules_group > li");
				var page_rule_properties_data 	   = new Array();

				if(page_rule_properties_elements.length >= 1){
					page_rule_properties_elements.each(function(index){
						page_rule_properties_data[index] = $(this).data('rule_properties');
					});
				}

				var page_rule_condition_elements = $("#ls_page_rules_group ul.ls_page_rules_conditions > li:not('.ls_add_condition')");
				var page_rule_condition_data 	= new Array();

				if(page_rule_condition_elements.length >= 1){
					page_rule_condition_elements.each(function(index){
						page_rule_condition_data[index] = $(this).data('rule_condition');
					});
				}

				//get email logic properties data
				var email_rule_properties_elements = $("#ls_email_rules_group > li");
				var email_rule_properties_data 	   = new Array();

				if(email_rule_properties_elements.length >= 1){
					email_rule_properties_elements.each(function(index){
						email_rule_properties_data[index] = $(this).data('rule_properties');
					});
				}

				var email_rule_condition_elements = $("#ls_email_rules_group ul.ls_email_rules_conditions > li:not('.ls_add_condition')");
				var email_rule_condition_data 	= new Array();

				if(email_rule_condition_elements.length >= 1){
					email_rule_condition_elements.each(function(index){
						email_rule_condition_data[index] = $(this).data('rule_condition');
					});
				}
				
				//do the ajax call to save the settings
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "save_logic_settings.php",
					   data: {
							  	form_id: $("#form_id").val(),
							  	logic_status: $(".logic_settings").data('logic_status'),
							  	field_rule_properties: field_rule_properties_data,
							  	field_rule_conditions: field_rule_condition_data,
							  	page_rule_properties: page_rule_properties_data,
							  	page_rule_conditions: page_rule_condition_data,
							  	email_rule_properties: email_rule_properties_data,
							  	email_rule_conditions: email_rule_condition_data
							  },
					   cache: false,
					   global: false,
					   dataType: "json",
					   error: function(xhr,text_status,e){
							   //error, display the generic error message		  
							   alert('Error! Unable to save logic settings. Please try again.');
					   },
					   success: function(response_data){
							   
						   if(response_data.status == 'ok'){
							   window.location.replace('manage_forms.php?id=' + response_data.form_id + '&hl=1');
						   }else{
							   alert('Error! Unable to save logic settings. Please try again.');
						   }
							   
					   }
				});
		}
		
		
		return false;
	});
	
    /***************************************************************************************************************/	
	/* 5. Initialize rule date pickers																			   */
	/***************************************************************************************************************/
	$("#ls_box_field_rules .rule_datepicker").each(function(index){
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1] + '_' + temp[2];

		$('#datepicker_' + element_id).datepick({ 
	    		onSelect: select_date,
	    		showTrigger: '#datepickimg_' + element_id
		});
	});

	$("#ls_box_page_rules .rule_datepicker").each(function(index){
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1] + '_' + temp[2];

		$('#datepicker_' + element_id).datepick({ 
	    		onSelect: select_date_page,
	    		showTrigger: '#datepickimg_' + element_id
		});
	});

	$("#ls_box_email_rules .rule_datepicker").each(function(index){
		var temp = $(this).attr('id').split('_');
		var element_id = temp[1] + '_' + temp[2];

		$('#datepicker_' + element_id).datepick({ 
	    		onSelect: select_date_email,
	    		showTrigger: '#datepickimg_' + element_id
		});
	});

	/***************************************************************************************************************/	
	/* 6. Page Logic (Skip Pages) Pane																			   */
	/***************************************************************************************************************/

	//attach event to 'enable rules to skip pages' checkbox
	$("#logic_page_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ls_box_page_rules .ls_box_content").slideDown();
			$(".logic_settings").data('logic_status').logic_page_enable = 1;
		}else{
			$("#ls_box_page_rules .ls_box_content").slideUp();
			$(".logic_settings").data('logic_status').logic_page_enable = 0;
		}
	});

	//attach event to 'select destination page' dropdown
	$('#ls_select_page_rule').bind('change', function() {
		
		if($(this).val() == ''){
			return true;
		}
		
		//val() could be page number or these: review, payment, success
		//we added 'page' prefix so that the generated id won't be the same with the show/hide logic fields
		var page_id = 'page' + $(this).val(); 

		//build the markup
		var li_markup = '';
		var condition_fieldname_markup = '';

		condition_fieldname_markup = $("#ls_fields_lookup").clone(false).attr("id","conditionpage_" + page_id + "_1").attr("name","conditionpage_" + page_id + "_1").show().outerHTML();

		li_markup 	+= 	'<li id="lipagerule_'+ page_id +'" style="display: none">' +
							'<table width="100%" cellspacing="0">' +
							  '<thead>' +
							    '<tr>' +
							      '<td>' + 
							         '<strong>' + $(this).find('option:selected').text() + '</strong><a class="delete_lipagerule" id="deletelipagerule_'+ page_id +'" href="#"><img src="images/icons/52_red_16.png"></a>' + 
							      '</td>' +
							    '</tr>' +
							  '</thead>' +
							  '<tbody>' +
							    '<tr>' +
							      '<td>' +
							      	'<h6>' +
							      		'<img src="images/icons/arrow_right_red.png" style="vertical-align: top" /> Go to this page if ' + 
										'<select style="margin-left: 5px;margin-right: 5px" name="pageruleallany_' + page_id + '" id="pageruleallany_' + page_id + '" class="element select rule_all_any">' + 
											'<option value="all">all</option>' +
											'<option value="any">any</option>' +
										'</select>' + 
										' of the following conditions match:' +
									'</h6>' +
							      	'<ul class="ls_page_rules_conditions">' +
							      		'<li id="lipagerule_' + page_id + '_1"> ' +
							      			 condition_fieldname_markup + ' ' +
											'<select name="conditiontext_'+ page_id +'_1" id="conditiontext_'+ page_id +'_1" class="element select condition_text" style="width: 120px;display: none">' +
												'<option value="is">Is</option>' +
												'<option value="is_not">Is Not</option>' +
												'<option value="begins_with">Begins with</option>' +
												'<option value="ends_with">Ends with</option>' +
												'<option value="contains">Contains</option>' +
												'<option value="not_contain">Does not contain</option>' +
											'</select>' + ' ' +
											'<select id="conditionnumber_'+ page_id +'_1" name="conditionnumber_' + page_id + '_1" style="width: 120px;display: none" class="element select condition_number">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="less_than">Less than</option>' + 
												'<option value="greater_than">Greater than</option>' + 
											'</select>' + ' ' +
											'<select id="conditiondate_'+ page_id +'_1" name="conditiondate_' + page_id + '_1" style="width: 120px;display: none" class="element select condition_date">' + 
												'<option value="is" selected="selected">Is</option>' + 
												'<option value="is_before">Is Before</option>' + 
												'<option value="is_after">Is After</option>' + 
											'</select>' + ' ' + 
											'<select id="conditioncheckbox_'+ page_id +'_1" name="conditioncheckbox_' + page_id + '_1" style="width: 120px;display: none" class="element select condition_checkbox">' + 
												'<option value="is_one">Is Checked</option>' + 
												'<option value="is_zero">Is Empty</option>' + 
											'</select>' + ' ' + "\n" +
											'<span name="conditiontime_' + page_id + '_1" id="conditiontime_'+ page_id + '_1" class="condition_time" style="display: none">' + 
												'<input name="conditiontimehour_' + page_id + '_1" id="conditiontimehour_' + page_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="HH"> : ' + 
												'<input name="conditiontimeminute_' + page_id + '_1" id="conditiontimeminute_' + page_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="MM">  ' + 
												'<span class="conditiontime_second" style="display:none"> : <input name="conditiontimesecond_' + page_id + '_1" id="conditiontimesecond_' + page_id + '_1" type="text" class="element text conditiontime_input" maxlength="2" size="2" value="" placeholder="SS"> </span>' + 
												'<select class="element select conditiontime_ampm conditiontime_input" name="conditiontimeampm_' + page_id + '_1" id="conditiontimeampm_' + page_id + '_1" style="display:none">' + 
													'<option selected="selected" value="AM">AM</option>' + 
													'<option value="PM">PM</option>' + 
												'</select>' + 
											'</span>' + 
											'<input type="text" class="element text condition_keyword" value="" id="conditionkeyword_'+ page_id +'_1" name="conditionkeyword_'+ page_id +'_1" style="display: none"> ' + "\n" +
											'<a href="#" id="deletecondition_' + page_id + '_1" name="deletecondition_'+ page_id +'_1" class="a_delete_condition"><img src="images/icons/51_red_16.png" /></a>' + "\n" +
							      		'</li>' +
							      		'<li class="ls_add_condition">' +
											'<a href="#" id="addcondition_'+ page_id +'" class="a_add_condition"><img src="images/icons/49_red_16.png" /></a>' +
										'</li>' +
							      	'</ul>' +
							      '</td>' +
							    '</tr>' +
							  '</tbody>' +
							'</table>' +
						'</li>';

		//append the rule markup
		$("#ls_page_rules_group").prepend(li_markup);
		$("#lipagerule_" + page_id).hide();

		//remove the current element from the list of condition
		$("#conditionpage_" + page_id + "_1 option[value=element_"+ page_id +"]").remove();
		$("#conditionpage_" + page_id + "_1 option[value^=element_"+ page_id +"_]").remove(); //remove childs element (for checkbox)

		//diplay the condition operator, depends on the first field on the field list
		var first_field_element_name = $("#conditionpage_" + page_id + "_1").eq(0).val();
		var first_field_element_type = $("#ls_fields_lookup").data(first_field_element_name);
		var default_condition = 'is';

		if(first_field_element_type == 'money' || first_field_element_type == 'number'){
			$("#conditionnumber_" + page_id + "_1").show();
			$("#conditionkeyword_" + page_id + "_1").show();
		}else if(first_field_element_type == 'date' || first_field_element_type == 'europe_date'){
			$("#conditiondate_" + page_id + "_1").show();
			$("#conditionkeyword_" + page_id + "_1").show();

			$("#lipagerule_" + page_id + "_1").addClass("condition_date");
		}else if(first_field_element_type == 'time' || first_field_element_type == 'time_showsecond' || first_field_element_type == 'time_24hour' || first_field_element_type == 'time_showsecond24hour'){
			$("#conditiondate_" + page_id + "_1").show();
			$("#conditiontime_" + page_id + "_1").show();
			
			if(first_field_element_type == 'time'){
				$("#conditiontimeampm_" + page_id + "_1").show();
			}else if(first_field_element_type == 'time_showsecond'){
				$("#conditiontimeampm_" + page_id + "_1").show();
				$("#conditiontimesecond_" + page_id + "_1").parent().show();
			}else if(first_field_element_type == 'time_showsecond24hour'){
				$("#conditiontimesecond_" + page_id + "_1").parent().show();
			}

		}else if(first_field_element_type == 'checkbox'){
			$("#conditioncheckbox_" + page_id + "_1").show();
			default_condition = 'is_one'
		}else{
			$("#conditiontext_" + page_id + "_1").show();
			$("#conditionkeyword_" + page_id + "_1").show();
		}

		//build the datepicker
		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_'+ page_id +'_1" id="datepicker_'+ page_id +'_1">' + "\n" +
							 	 ' <span style="display:none"><img id="datepickimg_'+ page_id +'_1" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + page_id + '_1').after(new_datepicker_tag);

		$('#datepicker_' + page_id + '_1').datepick({ 
		   		onSelect: select_date_page,
		   		showTrigger: '#datepickimg_' + page_id + '_1'
		});

		$("#lipagerule_" + page_id).slideDown();

		//attach dom data
		$("#lipagerule_" + page_id).data('rule_properties',{"page_id": page_id, "rule_all_any":"all"});
		$("#lipagerule_" + page_id + "_1").data('rule_condition',{"target_page_id": page_id,"element_name": first_field_element_name, "condition": default_condition,"keyword":""});

		//remove the option from the dropdown
		$(this).find('option:selected').remove();
		
		if($("#ls_select_page_rule option").length == 1){
			$("#ls_select_page_rule option").text('No More Page Available');
		}

	});

	//delegate change event to the all/any dropdown
    $('#ls_box_page_rules').delegate('select.rule_all_any', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#lipagerule_" + temp[1]).data('rule_properties').rule_all_any = $(this).val();
    });

    //delegate change event into condition field name dropdown
	$('#ls_box_page_rules').delegate('select.condition_fieldname', 'change', function(e) {
			
			var new_element_name = $(this).val();
			var new_element_type = $("#ls_fields_lookup").data(new_element_name);

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_checkbox,.condition_keyword').hide();
			$(this).parent().removeClass('condition_date');

			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'money' || new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'date' || new_element_type == 'europe_date'){
				$(this).parent().addClass('condition_date');
				$(this).parent().find('.condition_date,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'time' || new_element_type == 'time_showsecond' || new_element_type == 'time_24hour' || new_element_type == 'time_showsecond24hour'){
				$(this).parent().find('.condition_date,.condition_time').show();
				
				$(this).parent().find('.condition_time .conditiontime_second,.condition_time .conditiontime_ampm').hide();
				
				if(new_element_type == 'time'){
					$(this).parent().find('.condition_time .conditiontime_ampm').show();
				}else if(new_element_type == 'time_showsecond'){
					$(this).parent().find('.condition_time .conditiontime_ampm,.condition_time .conditiontime_second').show();
				}else if(new_element_type == 'time_showsecond24hour'){
					$(this).parent().find('.condition_time .conditiontime_second').show();
				}

				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'checkbox'){
				$(this).parent().find('.condition_checkbox').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_checkbox').val();
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('rule_condition').element_name = new_element_name;

    });
	
	//delegate change event to the condition type dropdown
    $('#ls_box_page_rules').delegate('select.condition_text,select.condition_number,select.condition_date,select.condition_checkbox', 'change', function(e) {
		$(this).parent().data('rule_condition').condition = $(this).val();
    });

    //delegate event to the condition keyword text
    $('#ls_box_page_rules').delegate('input.condition_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();	
    });

    //delegate event to the time condition inputs
    $('#ls_box_page_rules').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
		var temp = $(this).attr("id").split("_");

		var hour_value 	 = parseInt($("#conditiontimehour_" + temp[1] + "_" + temp[2]).val(),10);
		var minute_value = parseInt($("#conditiontimeminute_" + temp[1] + "_" + temp[2]).val(),10);
		var second_value = parseInt($("#conditiontimesecond_" + temp[1] + "_" + temp[2]).val(),10);
		var ampm_value 	 = $("#conditiontimeampm_" + temp[1] + "_" + temp[2]).val();

		if(isNaN(hour_value)){
			hour_value = '00';
		}

		if(isNaN(minute_value)){
			minute_value = '00';
		}
		
		if(isNaN(second_value)){
			second_value = '00';
		}

		$("#lipagerule_" + temp[1] + "_" + temp[2]).data('rule_condition').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });

    //attach event to 'delete page rules' icon
	$('#ls_box_page_rules').delegate('a.delete_lipagerule', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var page_id = temp[1];
		
		//restore field dropdown values
		$("#ls_select_page_rule").html($("#ls_select_page_rule_lookup").html());

		$("#lipagerule_" + page_id).fadeOut(400,function(){
			$(this).remove();

			$("#ls_page_rules_group > li").each(function(){
				var temp_name = $(this).attr('id').split('_');
				var cur_page_id = temp_name[1];

				cur_page_id = cur_page_id.substring(4); //remove the 'page' prefix
				
				$("#ls_select_page_rule option[value="+ cur_page_id +"]").remove();			
			});
		});
		
		return false;
	});

	//attach click event to 'add rule condition' (+) icon
	$('#ls_box_page_rules').delegate('a.a_add_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var page_id = temp[1];

		var new_id = $("#lipagerule_" + page_id + " ul > li:not('.ls_add_condition')").length + 1;
		var old_id = new_id - 1;

		//duplicate the last rule condition
		var last_rule_element = $("#lipagerule_" + page_id + " ul > li:not('.ls_add_condition')").last();
		last_rule_element.clone(false).data('rule_condition',$.extend('{}',last_rule_element.data('rule_condition'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			
			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + temp[1] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + temp[1] + "_" + new_id);
			
		}).end().attr("id","lipagerule_" + page_id + "_" + new_id).insertBefore("#lipagerule_" + page_id + " li.ls_add_condition").hide().fadeIn();

		//copy the value of the dropdowns
		$("#conditionpage_" + page_id + "_" + new_id).val($("#conditionpage_" + page_id + "_" + old_id).val());
		$("#conditiontext_" + page_id + "_" + new_id).val($("#conditiontext_" + page_id + "_" + old_id).val());
		$("#conditionnumber_" + page_id + "_" + new_id).val($("#conditionnumber_" + page_id + "_" + old_id).val());
		$("#conditiondate_" + page_id + "_" + new_id).val($("#conditiondate_" + page_id + "_" + old_id).val());
		$("#conditioncheckbox_" + page_id + "_" + new_id).val($("#conditioncheckbox_" + page_id + "_" + old_id).val());
		
		//reset the condition keyword  
		$("#conditionkeyword_" + page_id + "_" + new_id).val('');
		$("#lipagerule_" + page_id + "_" + new_id).data('rule_condition').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + page_id + '_' + new_id).next().next().remove();
		$('#datepicker_' + page_id + '_' + new_id).next().remove();
		$('#datepicker_' + page_id + '_' + new_id).remove();

		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_' + page_id + '_' + new_id +'" id="datepicker_' + page_id + '_' + new_id +'"> ' +
								 '<span style="display:none"> <img id="datepickimg_'+ page_id + '_' + new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + page_id + '_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + page_id + '_' + new_id).datepick({ 
	    		onSelect: select_date_page,
	    		showTrigger: '#datepickimg_' + page_id + '_' + new_id
		});

		return false;
	});

	//delegate click event to the 'delete rule condition' (-) icon
    $('#ls_box_page_rules').delegate('a.a_delete_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var page_id = temp[1];

		if($("#lipagerule_" + page_id + " ul > li:not('.ls_add_condition')").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all condition. <br />You must have at least one condition.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });

    /***************************************************************************************************************/	
	/* 7. Notification Emails Rules Pane					   												   	   */
	/***************************************************************************************************************/

	//attach event to 'enable rules to skip pages' checkbox
	$("#logic_email_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ls_box_email_rules .ls_box_content").slideDown();
			$(".logic_settings").data('logic_status').logic_email_enable = 1;
		}else{
			$("#ls_box_email_rules .ls_box_content").slideUp();
			$(".logic_settings").data('logic_status').logic_email_enable = 0;
		}
	});

	//delegate change event to the all/any dropdown
    $('#ls_box_email_rules').delegate('select.rule_all_any', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		$("#liemailrule_" + temp[1]).data('rule_properties').rule_all_any = $(this).val();
    });

    //delegate change event into condition field name dropdown
	$('#ls_box_email_rules').delegate('select.condition_fieldname', 'change', function(e) {
			
			var new_element_name = $(this).val();
			var new_element_type = $("#ls_fields_lookup").data(new_element_name);

			$(this).parent().find('.condition_text,.condition_time,.condition_number,.condition_date,.condition_checkbox,.condition_keyword').hide();
			$(this).parent().removeClass('condition_date');

			//display the appropriate condition type dropdown, depends on the field type
			//and make sure to update the condition property value when the field type has been changed
			if(new_element_type == 'money' || new_element_type == 'number'){
				$(this).parent().find('.condition_number,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_number').val();
			}else if(new_element_type == 'date' || new_element_type == 'europe_date'){
				$(this).parent().addClass('condition_date');
				$(this).parent().find('.condition_date,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'time' || new_element_type == 'time_showsecond' || new_element_type == 'time_24hour' || new_element_type == 'time_showsecond24hour'){
				$(this).parent().find('.condition_date,.condition_time').show();
				
				$(this).parent().find('.condition_time .conditiontime_second,.condition_time .conditiontime_ampm').hide();
				
				if(new_element_type == 'time'){
					$(this).parent().find('.condition_time .conditiontime_ampm').show();
				}else if(new_element_type == 'time_showsecond'){
					$(this).parent().find('.condition_time .conditiontime_ampm,.condition_time .conditiontime_second').show();
				}else if(new_element_type == 'time_showsecond24hour'){
					$(this).parent().find('.condition_time .conditiontime_second').show();
				}

				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_date').val();
			}else if(new_element_type == 'checkbox'){
				$(this).parent().find('.condition_checkbox').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_checkbox').val();
			}else{
				$(this).parent().find('.condition_text,input.text').show();
				$(this).parent().data('rule_condition').condition = $(this).parent().find('.condition_text').val();
			}

			$(this).parent().data('rule_condition').element_name = new_element_name;

    });
	
	//delegate change event to the condition type dropdown
    $('#ls_box_email_rules').delegate('select.condition_text,select.condition_number,select.condition_date,select.condition_checkbox', 'change', function(e) {
		$(this).parent().data('rule_condition').condition = $(this).val();
    });

    //delegate event to the condition keyword text
    $('#ls_box_email_rules').delegate('input.condition_keyword', 'keyup mouseout change', function(e) {
		$(this).parent().data('rule_condition').keyword = $(this).val();		
    });

    //delegate event to the time condition inputs
    $('#ls_box_email_rules').delegate('input.conditiontime_input,select.conditiontime_input', 'keyup mouseout change', function(e) {
		
		var temp = $(this).attr("id").split("_");

		var hour_value 	 = parseInt($("#conditiontimehour_" + temp[1] + "_" + temp[2]).val(),10);
		var minute_value = parseInt($("#conditiontimeminute_" + temp[1] + "_" + temp[2]).val(),10);
		var second_value = parseInt($("#conditiontimesecond_" + temp[1] + "_" + temp[2]).val(),10);
		var ampm_value 	 = $("#conditiontimeampm_" + temp[1] + "_" + temp[2]).val();

		if(isNaN(hour_value)){
			hour_value = '00';
		}

		if(isNaN(minute_value)){
			minute_value = '00';
		}
		
		if(isNaN(second_value)){
			second_value = '00';
		}

		$("#liemailrule_" + temp[1] + "_" + temp[2]).data('rule_condition').keyword = hour_value.toString() + ':' + minute_value.toString() + ':' + second_value.toString() + ':' + ampm_value;
    });

    //attach event to 'delete email rules' icon
	$('#ls_box_email_rules').delegate('a.delete_liemailrule', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];
		
		$("#liemailrule_" + rule_id).fadeOut(400,function(){
			$(this).remove();
		});
		
		return false;
	});

	//attach click event to 'add rule condition' (+) icon
	$('#ls_box_email_rules').delegate('a.a_add_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		var new_id = $("#liemailrule_" + rule_id + " ul > li:not('.ls_add_condition')").length + 1;
		var old_id = new_id - 1;

		//duplicate the last rule condition
		var last_rule_element = $("#liemailrule_" + rule_id + " ul > li:not('.ls_add_condition')").last();
		last_rule_element.clone(false).data('rule_condition',$.extend('{}',last_rule_element.data('rule_condition'))).find("*[id],*[name]").each(function() {
			var temp = $(this).attr("id").split("_"); 
			
			//rename the original id with the new id
			$(this).attr("id", temp[0] + "_" + temp[1] + "_" + new_id);
			$(this).attr("name", temp[0] + "_" + temp[1] + "_" + new_id);
			
		}).end().attr("id","liemailrule_" + rule_id + "_" + new_id).insertBefore("#liemailrule_" + rule_id + " li.ls_add_condition").hide().fadeIn();

		//copy the value of the dropdowns
		$("#conditionemail_" + rule_id + "_" + new_id).val($("#conditionemail_" + rule_id + "_" + old_id).val());
		$("#conditiontext_" + rule_id + "_" + new_id).val($("#conditiontext_" + rule_id + "_" + old_id).val());
		$("#conditionnumber_" + rule_id + "_" + new_id).val($("#conditionnumber_" + rule_id + "_" + old_id).val());
		$("#conditiondate_" + rule_id + "_" + new_id).val($("#conditiondate_" + rule_id + "_" + old_id).val());
		$("#conditioncheckbox_" + rule_id + "_" + new_id).val($("#conditioncheckbox_" + rule_id + "_" + old_id).val());
		
		//reset the condition keyword  
		$("#conditionkeyword_" + rule_id + "_" + new_id).val('');
		$("#liemailrule_" + rule_id + "_" + new_id).data('rule_condition').keyword = '';

		//remove the datepicker and rebuild it, with the events as well
		$('#datepicker_' + rule_id + '_' + new_id).next().next().remove();
		$('#datepicker_' + rule_id + '_' + new_id).next().remove();
		$('#datepicker_' + rule_id + '_' + new_id).remove();

		var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_' + rule_id + '_' + new_id +'" id="datepicker_' + rule_id + '_' + new_id +'"> ' +
								 '<span style="display:none"> <img id="datepickimg_'+ rule_id + '_' + new_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

		$('#conditionkeyword_' + rule_id + '_' + new_id).after(new_datepicker_tag);

		$('#datepicker_' + rule_id + '_' + new_id).datepick({ 
	    		onSelect: select_date_email,
	    		showTrigger: '#datepickimg_' + rule_id + '_' + new_id
		});

		return false;
	});

	//delegate click event to the 'delete rule condition' (-) icon
    $('#ls_box_email_rules').delegate('a.a_delete_condition', 'click', function(e) {
		var temp = $(this).attr('id').split('_');
		var rule_id = temp[1];

		if($("#liemailrule_" + rule_id + " ul > li:not('.ls_add_condition')").length <= 1){
			$("#ui-dialog-title-dialog-warning").html('Unable to delete!');
			$("#dialog-warning-msg").html("You can't delete all condition. <br />You must have at least one condition.");
			$("#dialog-warning").dialog('open');
		}else{
			$(this).parent().fadeOut(function(){
				$(this).remove();
			});
		}

		return false;
    });

    //delegate change event to the 'send email to' dropdown
    $('#ls_box_email_rules').delegate('select.target_email_dropdown', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		var target_email = $(this).val();
		
		if(target_email == 'custom'){
			$("#targetemailcustomspan_" + temp[1]).show();
			target_email = $("#targetemailcustom_" + temp[1]).val();
		}else{
			$("#targetemailcustomspan_" + temp[1]).hide();
		}

		$("#liemailrule_" + temp[1]).data('rule_properties').target_email = target_email;
		
    });

    //delegate event to the 'custom target email' text
    $('#ls_box_email_rules').delegate('input.target_email_custom', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').target_email = $(this).val();
    });

    //delegate change event to the 'email template' dropdown
    $('#ls_box_email_rules').delegate('select.template_name', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		
		$("#liemailrule_" + temp[1]).data('rule_properties').template_name = $(this).val();

		if($(this).val() == 'custom'){
			$("#ls_email_custom_template_div_" + temp[1]).fadeIn();
		}else{
			$("#ls_email_custom_template_div_" + temp[1]).fadeOut();
		}
    });

    //delegate change event to the 'custom from name' dropdown
    $('#ls_box_email_rules').delegate('select.custom_from_name_dropdown', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		var from_name = $(this).val();
		
		if(from_name == 'custom'){
			$("#customfromnamespan_" + temp[1]).show();
			from_name = $("#customfromnameuser_" + temp[1]).val();
		}else{
			$("#customfromnamespan_" + temp[1]).hide();
		}

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_from_name = from_name;
		
    });

    //delegate event to the 'custom from name' text
    $('#ls_box_email_rules').delegate('input.custom_from_name_text', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_from_name = $(this).val();
    });

    //delegate change event to the 'custom replyto email' dropdown
    $('#ls_box_email_rules').delegate('select.custom_replyto_email_dropdown', 'change', function(e) {
		var temp = $(this).attr("id").split("_");
		var replyto_email = $(this).val();
		
		if(replyto_email == 'custom'){
			$("#customreplytoemailspan_" + temp[1]).show();
			replyto_email = $("#customreplytoemailuser_" + temp[1]).val();
		}else{
			$("#customreplytoemailspan_" + temp[1]).hide();
		}

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_replyto_email = replyto_email;
		
    });

    //delegate event to the 'custom replyto email' text
    $('#ls_box_email_rules').delegate('input.custom_replyto_email_text', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_replyto_email = $(this).val();
    });

    //delegate event to the 'custom from email' text
    $('#ls_box_email_rules').delegate('input.custom_from_email', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_from_email = $(this).val();
    });

    //delegate event to the 'custom email subject' text
    $('#ls_box_email_rules').delegate('input.custom_email_subject', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_subject = $(this).val();
    });

    //delegate event to the 'custom email content' textarea
    $('#ls_box_email_rules').delegate('textarea.custom_email_content', 'keyup mouseout change', function(e) {
    	var temp = $(this).attr("id").split("_");

		$("#liemailrule_" + temp[1]).data('rule_properties').custom_content = $(this).val();
    });

    //attach event to 'plain text' checkbox
    $('#ls_box_email_rules').delegate('input.custom_plain_text', 'change', function(e) {
    	var temp = $(this).attr("id").split("_");

    	if($(this).prop("checked") == true){
			$("#liemailrule_" + temp[1]).data('rule_properties').custom_plain_text = 1;
		}else{
			$("#liemailrule_" + temp[1]).data('rule_properties').custom_plain_text = 0;
		}
    });

	//attach event to 'add email rule' link
	$("#ls_add_email_rule").click(function(){
		
		//duplicate the last rule group
		var last_rule_element = $("#ls_email_rules_group > li").last();
		var last_rule_id = last_rule_element.attr("id");

		last_rule_id_clean = parseInt(last_rule_id.replace("liemailrule_email",""));
		var new_rule_id    = last_rule_id_clean + 1;


		last_rule_element.clone(false).find("*[id],*[name]").each(function() {
			var current_id = $(this).attr("id"); 
			var new_id = current_id.replace("email" + last_rule_id_clean, "email" + new_rule_id);

			//rename the original id with the new id
			$(this).attr("id", new_id);
			$(this).attr("name", new_id);
		}).end().attr("id","liemailrule_email" + new_rule_id ).appendTo('#ls_email_rules_group').hide().fadeIn();

		//rename the new rule main label
		$("#liemailrule_email" + new_rule_id + " .rule_title").text("Rule #" + new_rule_id);

		//copy the data for main rule properties
		$("#liemailrule_email" + new_rule_id).data('rule_properties',$.extend('{}', last_rule_element.data('rule_properties')));
		$("#liemailrule_email" + new_rule_id).data('rule_properties').rule_id = new_rule_id;

		
		//copy the data from the previous conditions to the new one
		$("#liemailrule_email" + new_rule_id + " .ls_email_rules_conditions > li:not('.ls_add_condition')").each(function(){
			var current_condition_id = $(this).attr("id");
			var old_condition_id = current_condition_id.replace("email" + new_rule_id,"email" + last_rule_id_clean);

			//rule_condition data is an object, we need to use extend so that the object won't get passed by reference, screwing all other elements data
			$(this).data('rule_condition',$.extend('{}',$("#" + old_condition_id).data('rule_condition')));
			$(this).data('rule_condition').target_rule_id = 'email' + new_rule_id;

			//remove the datapicker and rebuild it, with the events as well
			var current_datepicker = $(this).find(".hasDatepick");
			var current_datepicker_id = current_datepicker.attr("id");

			current_datepicker.next().next().remove();
			current_datepicker.next().remove();
			current_datepicker.remove();

			var temp = current_datepicker_id.split("_");
			var condition_id = temp[2];

			var new_datepicker_tag = ' <input type="hidden" value="" name="datepicker_email' + new_rule_id + '_' + condition_id +'" id="datepicker_email' + new_rule_id + '_' + condition_id +'"> ' +
									 '<span style="display:none"> <img id="datepickimg_email'+ new_rule_id + '_' + condition_id +'" alt="Pick date." src="images/icons/calendar.png" class="trigger condition_date_trigger" style="vertical-align: top; cursor: pointer" /></span>';

			$('#conditionkeyword_email' + new_rule_id + '_' + condition_id).after(new_datepicker_tag);

			$('#datepicker_email' + new_rule_id + '_' + condition_id).datepick({ 
		    		onSelect: select_date_email,
		    		showTrigger: '#datepickimg_email' + new_rule_id + '_' + condition_id
			});
		});

		//copy all dropdown selection state
		var original_rule_dropdowns = $("#liemailrule_email" + last_rule_id_clean).find("select");
		var new_rule_dropdowns =  $("#liemailrule_email" + new_rule_id).find("select");
		new_rule_dropdowns.each(function(index,item){
			//set new select to value of old select
     		$(item).val(original_rule_dropdowns.eq(index).val());
		});

		return false;

	});

	/***************************************************************************************************************/	
	/* 8. Dialog Box for template variable																		   */
	/***************************************************************************************************************/
	
	$("#dialog-template-variable").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Close',
				id: 'btn-change-theme-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	$("a.tempvar_link").click(function(){
		$("#dialog-template-variable").dialog('open');
		return false;
	});

	$("#tempvar_help_trigger a").click(function(){
		if($(this).text() == 'more info'){
			$(this).text('hide info');
			$("#tempvar_help_content").slideDown();
			$("#tempvar_value").effect("pulsate", { times:3 }, 1500);
		}else{
			$(this).text('more info');
			$("#tempvar_help_content").slideUp();
		}
		return false;
	});

	//attach event to template variable dropdown
	$('#dialog-template-variable-input').bind('change', function() {
		$("#tempvar_value").text('{' + $(this).val() + '}');
	});


});
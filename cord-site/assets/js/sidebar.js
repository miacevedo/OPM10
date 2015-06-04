window.addEvent('domready', function() {

			SqueezeBox.initialize({});
			SqueezeBox.assign($$('a.modal'), {
				parse: 'rel'
			});
		});
var K2SitePath = '/';
var paneltype = 'opacity';
		jQuery(document).ready(function(){
			jQuery('#navwrap').addClass('absolute');
		});
	
jQuery(document).ready(function(){		jQuery('.moduletable-superfish ul,#nav ul')
			.supersubs({ 
       		minWidth:    '16',   // minimum width of sub-menus in em units 
    			maxWidth:    '21',   // maximum width of sub-menus in em units 
				disableHI:   true,  // set to true to disable hoverIntent detection
				extraWidth:  1     // 
			})
			.superfish({
				animation : {opacity:"show"},
				speed:       'normal',
				delay : 800 
			});	

		jQuery('.moduletable-panelmenu ul ul').hide()
		jQuery('.moduletable-panelmenu span').click(function() {
		jQuery('.moduletable-panelmenu span').removeClass('open');
		jQuery('.moduletable-panelmenu ul ul').slideUp();
		jQuery(this).next('ul').slideUp('normal');
		if(jQuery(this).next().is(':hidden') == true) {
		jQuery(this).addClass('open');
		jQuery(this).next().slideDown('normal');
		 } 
		});
			jQuery('.moduletable-panelmenu ul ul:first').slideDown();
			jQuery('.moduletable-panelmenu ul li span:first').addClass('open');
		jQuery('.moduletable-panelmenu ul li#current ul,.moduletable-panelmenu ul li.active ul').slideDown()
			jQuery('.moduletable-panelmenu ul li.active span').addClass('open');
		jQuery('.moduletable-panelmenu ul ul span').click(function() {
		jQuery('.moduletable-panelmenu ul ul ul').slideUp();
		});

});
// Hidden Panel
		
jQuery(document).ready(function(){	
	jQuery("#zenoverlay").hide();
	jQuery("#zenpanelopen,#zenpanelclose,#zenpanelclose2,#zenoverlay").click(function(){
			
			
			if (paneltype == 'opacity') {
     			jQuery("#zenpanel,#zenoverlay").animate({opacity: "toggle"}, 400);
  			}		
  			if (paneltype == 'width') {
     			jQuery("#zenpanel,#zenoverlay").animate({width: "toggle"}, 400);
  			}
  			if (paneltype == 'height') {
     			jQuery("#zenpanel,#zenoverlay").animate({height: "toggle"}, 400);
  			}

			jQuery("a#zenpanelclose").toggleClass("active");
			jQuery("a#zenpanelopen").toggleClass("active");
		return false;
	});
	
	// Centers the hidden panel
		jQuery.fn.center = function () {
	   	 this.css("position","absolute");
	   	 this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
	   	 this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
	   	 return this;
		}

		jQuery("#zenpanel").center();

		jQuery(window).resize(function(){
			window_width = jQuery(window).width();
			window_height = jQuery(window).height();

			jQuery("#zenpanel").each(function(){
					var modal_height = jQuery(this).outerHeight();
					var modal_width = jQuery(this).outerWidth();
					var top = (window_height-modal_height)/2;
					var left = (window_width-modal_width)/2;
				jQuery(this).css({'top' : top , 'left' : left});
			});
		});
	

});
	jQuery(document).ready(function(){
			// Create the dropdown base
jQuery("<select />").appendTo("#mobilemenu");
var mobileMenuTitle = jQuery("#mobilemenu").attr("title");
// Create default option "Go to..."
jQuery("<option />", {
   "selected": "selected",
   "value"   : "",
   "text"    : mobileMenuTitle
}).appendTo("#mobilemenu select");

// Populate dropdown with menu items
jQuery("#nav ul.menu>li>a, #nav ul.menu>li>span.mainlevel,#nav ul.menu>li>span.separator").each(function() {
 var el = jQuery(this);
 jQuery("<option />", {
     "value"   : el.attr("href"),
     "text"    : el.text()
     
 }).appendTo("#mobilemenu select");
getSubMenu(el);
});

function getSubMenu(el){
	var subMenu = jQuery('~ ul>li>a',el);
	var tab = "- ";
	if (!(subMenu.length === 0)){
		subMenu.each(function(){
			var sel = jQuery(this);
			var nodeval = tab + sel.text();
			 jQuery("<option />", {
			     "value"   : sel.attr("href"),
			     "text"    : nodeval

			 }).appendTo("#mobilemenu select");
			getSubMenu(sel);
		});
	}
}
 // To make dropdown actually work
          // To make more unobtrusive: http://css-tricks.com/4064-unobtrusive-page-changer/
      jQuery("#mobilemenu select").change(function() {
        window.location = jQuery(this).find("option:selected").val();
      });

	// Equal Heights Script
	function equalHeight(group) {
	var tallest = 0;
	group.each(function() {
	    jQuery(this).css('height','');
	    var thisHeight = jQuery(this).height();
	    if(thisHeight > tallest) {
	        tallest = thisHeight;
	    }
	});
	group.css('height',tallest);
	}

	function calculateEQ(){
		equalHeight(jQuery("#bottom .moduletable"));
	}

	// mit license. paul irish. 2010.
	// webkit fix from Oren Solomianik. thx!
	// callback function is passed the last image to load
	//   as an argument, and the collection as `this`


	jQuery.fn.imagesLoaded = function(callback){
	  var elems = this.filter('img'),
	      len   = elems.length,
	      blank = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";

	  elems.bind('load',function(){
	      if (--len <= 0 && this.src !== blank){ callback.call(elems,this); }
	  }).each(function(){
	     // cached images don't fire load sometimes, so we reset src.
	     if (this.complete || this.complete === undefined){
	        var src = this.src;
	        // webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
	        // data uri bypasses webkit log warning (thx doug jones)
	        this.src = blank;
	        this.src = src;
	     }  
	  }); 
	  return this;
	};

	jQuery('img').imagesLoaded(function() { 
			calculateEQ();

	});
	

	// Determines width assigned to the menu items
	var count = 100 / jQuery("#nav ul.menu > li").size();
	jQuery('#nav ul.menu > li').css( "width", count +'%');
	
});
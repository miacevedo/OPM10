var jMachform = jQuery.noConflict();
(function($) { 
  $(function() {
    var mf_iframe_height;
      
    var mf_iframe = $('<iframe onload="javascript:parent.scrollTo(0,0);" height="' + __machform_height + '" allowTransparency="true" frameborder="0" scrolling="no" style="width:100%;border:none" src="'+ __machform_url +'"><a href="'+ __machform_url +'">View Form</a></iframe>');
    $("#mf_placeholder").after(mf_iframe);
    $("#mf_placeholder").remove();

    $.receiveMessage(function(e){      
      if(e.data.indexOf('run_safari_cookie_fix') != -1){
        //execute safari cookie fix
        var mf_folder = __machform_url.substring(0,__machform_url.lastIndexOf('/'));
        
        window.location.href = mf_folder + '/safari_init.php?ref=' + window.btoa(window.location.href);
        return;
      }else{
        //adjust the height of the iframe     
        var new_height = Number( e.data.replace( /.*mf_iframe_height=(\d+)(?:&|$)/, '$1' ) );
        if (!isNaN(new_height) && new_height > 0 && new_height !== mf_iframe_height) {
          mf_iframe.height(mf_iframe_height = new_height); //height has changed, update the iframe
        }
      }
      
    });
  });
})(jMachform);
<div class="clear"></div>
	
	</div><!-- /#main -->
	<img src="images/bottom.png" id="bottom_shadow">
	<div id="footer">
		
		<p class="copyright">Copyright &copy; <a href="http://www.appnitro.com">Appnitro Software</a> 2007-2014</p>
		<div class="clear"></div>
		
	</div><!-- /#footer -->
	

</div><!-- /#container -->

</div><!-- /#bg -->

<?php
	if($disable_jquery_loading !== true){
		echo '<script type="text/javascript" src="js/jquery.min.js"></script>';
	}
?>

<?php if(!empty($footer_data)){ echo $footer_data; } ?>
</body>
</html>
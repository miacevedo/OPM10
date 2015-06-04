<?php
/*
 Template Name: Free Estimate Page Template
 *
*/
?>
<?php get_header(); ?>
<div id="mainwrap">
    <div class="container center">
        <div class="row">
            <div class="inner">
                <div class="twoL" id="main">
                    <!-- Breadcrumb -->
					
					<?php the_breadcrumb(); ?>

                    <div class="clear"></div><!-- End Breadcrumb -->
                    
                    <!-- Main Content -->
					<div class="grid_eight twoL" id="midCol" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">
						<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
						
					    <div class="clear"></div>
					
					    <div class="twoL" id="mainContent" role="article" itemscope itemtype="http://schema.org/BlogPosting">
						  <div itemprop="articleBody">			
				          
				            <h1 class="contentheading" itemprop="headline"><?php the_title(); ?></h1>
									
							<?php the_content(); ?>
							
						  </div>
						  
						   <div id="system-message-container"></div>
						  
						  <script type="text/javascript">
							function iFrameHeight() {
								var h = 0;
								if (!document.all) {
									h = document.getElementById('blockrandom').contentDocument.height;
									document.getElementById('blockrandom').style.height = h + 60 + 'px';
								} else if (document.all) {
									h = document.frames('blockrandom').document.body.scrollHeight;
									document.all.blockrandom.style.height = h + 20 + 'px';
								}
							}
							</script>
							
							<div class="contentpane">
								
							  <h1>Five Free Boxes with Every Free Estimate</h1>
							
							  <!--
							  <iframe onload="iFrameHeight()" id="blockrandom" name="iframe" src="http://cordmoving.staging.wpengine.com/machform/embed.php?id=1" width="100%" height="850" scrolling="auto" class="wrapper">
								This option will not work correctly. Unfortunately, your browser does not support inline frames.
							  </iframe>
							  -->
							  
							</div>
						  
						  
					    </div>
					
					    <div class="clear"></div>
					    
					    <?php endwhile; endif; ?>
					    
					</div><!-- End Main Content -->
                    
                    <?php get_sidebar(1); ?>

                   
                    
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
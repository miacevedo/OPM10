<?php
/*
 Template Name: Full Width Page Template
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
                    
                    
                    <div class="grid_twelve one" id="midCol">
				    <div class="clear"></div>
				
				    <div class="one" id="mainContent">
				        <div id="system-message-container"></div>
				
				        <div id="jbArticle mainContent post-<?php the_ID(); ?>" role="article">
				            <!-- Item Title -->
							
							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
							
				            <h1 class="contentheading"><?php the_title(); ?></h1>
				
				            <div class="jbIntroText">
							
				                <?php the_content(); ?>
								
								<div class="clear"></div>
		                    
				            </div>
				            
				            <?php endwhile; endif; ?>
				            
				        </div>
				    </div>
				
				    <div class="clear"></div>
				</div><!-- End Main Content -->

                    
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
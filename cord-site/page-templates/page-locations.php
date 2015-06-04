<?php
/*
 * Template Name: Location Page Template
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
						<?php if (have_posts()) : ?>
						
					    <div class="clear"></div>
					
					    <div class="twoL" id="mainContent" role="article" itemscope itemtype="http://schema.org/BlogPosting">
						  <div itemprop="articleBody">			
				            
				            <?php while (have_posts()) : the_post(); ?>
				            
					            <h1 itemprop="headline" class="contentheading">
						            <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
						        </h1>
									
								<?php the_content(); ?>
							
							<?php endwhile; ?>
							
						  </div>
					
					    <div class="clear"></div>
					    
					    <?php 
						    $wp_query = new WP_query( array(
						   	 'post_type' => 'locations',
						   	 'posts_per_page' => -1,
						   	 'orderby' => 'date',
						   	 'order'   => 'DESC'
						   	 ));
						   	 
						    if ( have_posts() ) : while ( have_posts() ) : the_post(); 
						?>								
							
							<div itemprop="articleBody" class="resultsblock">			
					            
					            <h2><strong>
						            <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
							            <?php the_title(); ?>
							        </a>
					            </strong></h2>
										
								<?php the_content(); ?>
																
							</div>
							  
							<?php endwhile; endif; wp_reset_postdata();?>
							
							
					    </div>
					    
					    <?php endif; ?>
					</div><!-- End Main Content -->
                    
                    <?php get_sidebar(1); ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
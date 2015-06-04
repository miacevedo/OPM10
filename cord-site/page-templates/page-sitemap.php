<?php
/*
 Template Name: Site Map Page Template
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
					<h1 class="contentheading" itemprop="headline"><?php the_title(); ?></h1>
					    <h3>Pages</h3>  
						<ul>
							<?php wp_list_pages("title_li=" ); ?>
						</ul>  
						
						<h3>Archives</h3>  
					    <ul>  
					        <?php wp_get_archives('type=monthly&show_post_count=true'); ?>  
					    </ul>  
						    
						<h3>Custom Type</h3>
						<ul>
							<?php 
						    $wp_query = new WP_query( array(
						   	 'post_type' => 'custom_type',
						   	 'posts_per_page' => -1
						   	 ));
						   
						    if ( have_posts() ) : while ( have_posts() ) : the_post(); 
						    ?>
								
							<li> 
							  <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title_attribute(); ?></a>
							  </a>
							</li>
											
				        <?php endwhile; endif; wp_reset_postdata();?>
						</ul>
					    
					</div>
					<!-- End Main Content -->
                    
                    <?php get_sidebar(1); ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
<?php
/*
 * Locations POST TYPE TEMPLATE */
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
												
					    <div class="clear"></div>
					
					    <div class="twoL" id="mainContent post-<?php the_ID(); ?>" role="article">
						  <div itemprop="articleBody">			
				          	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				          	
				          	<h1 class="contentheading ">Cord Moving &amp; Storage – <?php the_title(); ?></h1>
				          								
							<?php the_content(); ?>
							
							<?php endwhile; endif; ?>
							<div class="clear"></div>
							
							<p  style="margin-top: 20px;">Cord Moving and Storage of <?php the_title(); ?> serves the following areas: 
							
							<?php 
						    
						      $query = new WP_Query( array ( 'post_type' => 'locations', 'posts_per_page' => -1 ) );
							  while ( $query->have_posts() ) : $query->the_post();
						    
						    ?>
						    
						    	<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title_attribute(); ?></a>, 
						    	
								<?php endwhile; wp_reset_postdata();?>
							</p>
							
							<div class="clear"></div>
							
		                    <ul class="pagenav">
			                    <li class="pagenav-prev">
									<?php

									  $prevPost = get_adjacent_post(false,'',true);
									    //var_dump($prevPost);
									    
									    if($prevPost) {
									        	 
										 ?>
											    
										  <a href="<?php echo get_site_url().'/locations/'.$prevPost->post_name; ?>">
											&lt; Prev
									      </a>
									      									          
									    <?php }  else { //end if 
									        
									      $firstPost = new WP_query('post_type=locations&posts_per_page=1&order=DESC');
									      $firstPost->the_post();
									
									      ?>
									      
									      <a href="<?php the_permalink(); ?>">
									        &lt; Prev
									      </a>
									
									      <?php wp_reset_query(); 
									
									    } 
									    
									?> 									
								</li>


		                        <li class="pagenav-next">
		                        	<?php
								    	$nextPost = get_adjacent_post(false,'',false);
									      //var_dump($nextPost);   
									
									      if($nextPost) {
									  	?>
									
									  		<a href="<?php echo get_site_url().'/locations/'.$nextPost->post_name; ?>">
									      		Next &gt;
									        </a>
									
									     
									      <?php } else {  
									
									        $lastPost = new WP_query('post_type=locations&posts_per_page=1&order=ASC');
									        $lastPost->the_post();
									
									        ?>
									
									        <a href="<?php the_permalink(); ?>">
									          Next &gt;
									        </a>
									               
									        <?php wp_reset_query(); 
									      } 
								    ?>
		                        </li>
		                    </ul>							
							
						  </div>

					    </div>
					
					    <div class="clear"></div>
					    
					    
					</div><!-- End Main Content -->
                    
                    <?php get_sidebar(1); ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
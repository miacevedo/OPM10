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
                    
                    
                    <div class="grid_twelve one" id="midCol">
				    <div class="clear"></div>
				
				    <div class="one" id="mainContent">
				        <div id="system-message-container"></div>
				
				        <div id="jbArticle" id="mainContent post-<?php the_ID(); ?>" role="article">
				            <!-- Item Title -->
							
							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
							
				            <h1 class="contentheading"><?php the_title(); ?></h1>
				
				            <div class="jbIntroText">
							
				                <?php the_content(); ?>
								
								<div class="clear"></div>
								
				                <ul class="pagenav">
				                    <li class="pagenav-prev">
										<?php
	
										  $prevPost = get_adjacent_post(false,'',true);
										    //var_dump($prevPost);
										    
										    if($prevPost) {
										        	 
											 ?>
												    
											  <a href="<?php echo get_site_url().'/service-area/'.$prevPost->post_name; ?>">
												&lt; Prev
										      </a>
										      									          
										    <?php }  else { //end if 
										        
										      $firstPost = new WP_query('post_type=service-areas&posts_per_page=1&order=DESC');
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
										
										  		<a href="<?php echo get_site_url().'/service-area/'.$nextPost->post_name; ?>">
										      		Next &gt;
										        </a>
										
										     
										      <?php } else {  
										
										        $lastPost = new WP_query('post_type=service-areas&posts_per_page=1&order=ASC');
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
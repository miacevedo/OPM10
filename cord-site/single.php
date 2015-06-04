<?php get_header(); ?>
<div id="mainwrap">
    <div class="container center">
        <div class="row">
            <div class="inner">
                <div class="twoL" id="main">
	                
	                <?php the_breadcrumb(); ?>

                    <div class="clear"></div><!-- End Breadcrumb -->
                    
                    <!-- Main Content -->
					<div class="grid_eight twoL" id="midCol">
				        <div class="clear"></div>
				
				        <div class="twoL" id="mainContent" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">
				
				            <div id="jbArticle post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article" itemscope itemprop="blogPost" itemtype="http://schema.org/BlogPosting">
				                
				                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

									<h1 class="contentheading" itemprop="headline" rel="bookmark"><?php the_title(); ?></h1>

					                <div class="jbIntroText" itemprop="articleBody">
					                    
					                    <?php the_content(); ?>
										
										<div class="clear"></div>
										
					                    <ul class="pagenav" style="margin-bottom: 30px;">
						                    <li class="pagenav-prev">
												<?php previous_post_link( '%link', '&lt; Prev' ); ?>
											</li>
					
					                        <li class="pagenav-next">
					                            <?php next_post_link( '%link', 'Next &gt;' ); ?>
					                        </li>
					                    </ul>
					                    
					                    <div class="clear"></div>			
					                    <?php comments_template(); ?>
					                </div>
		
								<?php endwhile; ?>
								
								
								<?php else : ?>
		
									<h1 class="contentheading"><?php _e( 'Oops, Post Not Found!', 'bonestheme' ); ?></h1>
				
					                <div class="jbIntroText">
					                    <!-- Introtext -->
					                    <img alt="" src="/images/trailers-mechanics-technicians-jobs-hiring.jpg">
					
					                    <h2><?php _e( 'Uh Oh. Something is missing. Try double checking things.', 'bonestheme' ); ?></h2>
					
					                    <p><?php _e( 'This is the error message in the single.php template.', 'bonestheme' ); ?></p>
					                    
					                </div>
		
								<?php endif; ?>
				                
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
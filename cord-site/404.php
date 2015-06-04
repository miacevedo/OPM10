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
				          
				            <h1 class="contentheading" itemprop="headline"><?php _e( 'Epic 404 - Article Not Found', 'bonestheme' ); ?></h1>
									
							<p><?php _e( 'The article you were looking for was not found, but maybe try looking again!', 'bonestheme' ); ?></p>
							
						  </div>
						  <?php comments_template(); ?>
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
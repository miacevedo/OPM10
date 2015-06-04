<?php get_header(); ?>
<div id="mainwrap">
    <div class="container center">
        <div class="row">
            <div class="inner">
                <div class="twoL" id="main">

                    <?php the_breadcrumb(); ?>

                    <div class="clear"></div><!-- End Breadcrumb -->
                    
					<!-- Main Content -->
                    <div class="grid_eight twoL" id="midCol" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">
	                    <?php
						  the_archive_title( '<h1 class="archive-title">', '</h1>' );
						  the_archive_description( '<div class="taxonomy-description">', '</div>' );
						?>
				
                        <div class="clear"></div>

                        <div class="twoL" id="mainContent">

                            <div class="search">
                                <div id="searchpage" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">
	                                
	                                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	                                
                                    <div class="resultsblock" id="post-<?php the_ID(); ?>" role="article">
	                                    
                                        <h2 class="resulttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
										
										<?php printf( '<p><span class="small">' . __('', 'bonestheme' ) . '%1$s</span></p>' , get_the_category_list(', ') ); ?>
										
                                        <p class="result-text"><?php the_excerpt(); ?></p>
                                        
                                        <span class="small vcard">
                                        	<?php printf( __( 'Created on', 'bonestheme' ).' %1$s %2$s',
                       								'<time class="updated entry-time" datetime="' . get_the_time('Y-m-d') . '" itemprop="datePublished">' . get_the_time(get_option('date_format')) . '</time>',
                       								''
                    						); ?>
                                        </span>
                                        
                                    </div>
                                                                        
                                    <?php endwhile; ?>

									<?php bones_page_navi(); ?> <!-- <div class="pagination"></div> -->

									<?php else : ?>
									
									<div class="resultsblock">
	                                    
                                        <p class="result-text"><?php _e( 'Oops, Post Not Found!', 'bonestheme' ); ?></p>
                                        
                                    </div>								

									<?php endif; ?>
                                    
                                </div>
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
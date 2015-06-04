<?php
/*
 Template Name: Services Map Page Template
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
					
					    <div class="clear"></div>
					
					    <div class="twoL" id="mainContent">
					        <div id="system-message-container"></div>
					
					        <div class="blog">
					            <div class="items-leading">
					                <div class="leading-0">
					                    <div class="jbSection">
					                        <!-- Title -->
					
					                        <h1 class="contentheading"><a class="contentpagetitle" href="<?php echo home_url(); ?>/services/residential">Residential Moving St. Louis</a></h1>
					                        <!-- Begin JB Category Text -->
					
					                        <div class="jbCategoryText">
					                            <p><a href="<?php echo home_url(); ?>/services/residential"><img alt="Residential Moving" border="0" src="<?php echo get_template_directory_uri(); ?>/assets/images/residential.png" style="border: 0;"></a></p>
					                        </div><!-- End JB Category Text -->
					                    </div>
					
					                    <div class="item-separator"></div>
					                </div>
					            </div>
					
					            <div class="items-row cols-2 row-0">
					                <div class="item column-1">
					                    <div class="jbSection">
					                        <!-- Title -->
					
					                        <h1 class="contentheading"><a class="contentpagetitle" href="<?php echo home_url(); ?>/services/commercial">Commercial Moving Saint Louis</a></h1>
					                        <!-- Begin JB Category Text -->
					
					                        <div class="jbCategoryText">
					                            <p><a href="<?php echo home_url(); ?>/services/commercial"><img alt="Commercial Moving Company St. Louis" border="0" src="<?php echo get_template_directory_uri(); ?>/assets/images/commercial.png" style="border: 0;"></a></p>
					                        </div><!-- End JB Category Text -->
					                    </div>
					
					                    <div class="item-separator"></div>
					                </div>
					
					                <div class="item column-2">
					                    <div class="jbSection">
					                        <!-- Title -->
					
					                        <h1 class="contentheading"><a class="contentpagetitle" href="<?php echo home_url(); ?>/services/storage">Storage &amp; Warehouse</a></h1><!-- Begin JB Category Text -->
					
					                        <div class="jbCategoryText">
					                            <p><a href="<?php echo home_url(); ?>/services/storage"><img alt="" border="0" src="<?php echo get_template_directory_uri(); ?>/assets/images/storage.png"></a></p>
					                        </div><!-- End JB Category Text -->
					                    </div>
					
					                    <div class="item-separator"></div>
					                </div><span class="row-separator"></span>
					            </div>
					
					            <div class="items-row cols-2 row-1">
					                <div class="item column-1">
					                    <div class="jbSection">
					                        <!-- Title -->
					
					                        <h1 class="contentheading"><a class="contentpagetitle"
					                        href="<?php echo home_url(); ?>/services/international">International Moving</a></h1><!-- Begin JB Category Text -->
					
					                        <div class="jbCategoryText">
					                            <p><a href="<?php echo home_url(); ?>/services/international"><img alt="International Moving" border="0" src="<?php echo get_template_directory_uri(); ?>/assets/images/international.png" style="border: 0;"></a></p>
					                        </div><!-- End JB Category Text -->
					                    </div>
					
					                    <div class="item-separator"></div>
					                </div>
					
					                <div class="item column-2">
					                    <div class="jbSection">
					                        <!-- Title -->
					
					                        <h1 class="contentheading"><a class="contentpagetitle" href="<?php echo home_url(); ?>/services/specialty">Specialty Services</a></h1><!-- Begin JB Category Text -->
					
					                        <div class="jbCategoryText">
					                            <p><a href="<?php echo home_url(); ?>/services/specialty"><img border="0" src="<?php echo get_template_directory_uri(); ?>/assets/images/specialservices.png" style="border: 0;"></a></p>
					                        </div><!-- End JB Category Text -->
					                    </div>
					
					                    <div class="item-separator"></div>
					                </div><span class="row-separator"></span>
					            </div>

					            
					        </div>
					    </div>
					
					    <div class="clear"></div>

					    
					</div>
					<!-- End Main Content -->
                    
                    <?php get_sidebar(2); ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
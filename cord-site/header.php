<!doctype html>

<!--[if lt IE 7]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if (IE 7)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--> <html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->

	<head>
		<meta charset="utf-8">

		<?php // force Internet Explorer to use the latest rendering engine available ?>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<title><?php wp_title(''); ?></title>

		<?php // mobile meta (hooray!) ?>
		<meta name="HandheldFriendly" content="True">
		<meta name="MobileOptimized" content="320">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>

		<link rel="icon" href="<?php echo get_template_directory_uri(); ?>/assets/ico/favicon.ico">
		
		<?php wp_head(); ?>
		<?php // drop Google Analytics Here ?>
		<?php // end analytics ?>
		
	</head>

<body <?php body_class($class); ?> itemscope itemtype="http://schema.org/WebPage">
		
	<div id="fontHeadings" class="georgia">

	<div class="fullwrap">

<!-- Nav wrapper -->
<div id="navwrap">
    <div class="container center">
        <div class="row">
            <div class="inner">
                <div id="navwrapper">
                    <div class="grid_twelve helvetica left" id="nav">
                        <div class="hide" id="menuwrap">
                            <div class="moduletable-superfish">
                                <div class="jbmoduleBody">

						            <nav role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
					                <?php wp_nav_menu(array(
					                         'container' => false,                           // remove nav container
					                         'container_class' => '',                 // class of container (should you choose to use it)
					                         'menu' => __( 'The Main Menu', 'bonestheme' ),  // nav name
					                         'menu_class' => 'menu',               // adding custom nav class
					                         'theme_location' => 'main-nav',                 // where it's located in the theme
					                         'before' => '',                                 // before the menu
					                           'after' => '',                                  // after the menu
					                           'link_before' => '',                            // before each link
					                           'link_after' => '',                             // after each link
					                           'depth' => 0,                                   // limit the depth of the nav
					                         'fallback_cb' => ''                             // fallback function (if there is one)
					                )); ?>
					
					            	</nav>
                                    
                                </div>
                            </div><!-- /.moduletable-superfish -->
                        </div><!-- /#menuwrap -->

                        <div id="mobilemenu" title="Main Menu"></div>
                        <!-- <div id="callus" style="height:100px;"><h1><font color="grey"> Call 1(866) 542-0802</h1></font></div> -->

                        <div class="overlay" id="zenpanel">
                            <a href="#" id="zenpanelclose2" rel=
                            "#panelInner">Less</a>

                            <div id="zenpanelInner">
                                <div class="grid_3" id="panel1">
                                    <div class="moduletable color4">
                                        <div class="moduleTitle">
                                            <h3><span>Popular
                                            Items</span></h3>
                                        </div>

                                        <div class="jbmoduleBody">
                                            <ul class="mostread color4">
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid_3" id="panel2">
                                    <div class="moduletable color3">
                                        <div class="moduleTitle">
                                            <h3><span>Recent Items</span></h3>
                                        </div>

                                        <div class="jbmoduleBody">
                                            <ul class="latestnews color3">
                                                <li>
                                                    <a href="/town-and-country-mo-movers">Town and Country MO Movers</a>
                                                </li>

                                                <li>
                                                    <a href="/smithton-il-movers">Smithton IL Movers Moving Company Cord Moving & Storage</a>
                                                </li>

                                                <li>
                                                    <a href="/mascoutah-il-movers">Mascoutah IL Movers Moving Company Cord Moving & Storage</a>
                                                </li>

                                                <li>
                                                    <a href="/st-louis-mo-movers">St. Louis MO Movers Moving Company Cord Moving & Storage</a>
                                                </li>

                                                <li>
                                                    <a href="/columbia-il-movers">Columbia IL Movers Moving Company Cord Moving & Storage</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid_3 zenlast" id="panel3">
                                    <div class="moduletable color3">
                                        <div class="moduleTitle">
                                            <h3><span>Other News</span></h3>
                                        </div>

                                        <div class="jbmoduleBody">
                                            <div class="newsflash color3">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="clear">
                                &nbsp;
                            </div><span class="stripe1">&nbsp;</span>
                            <span class="stripe2">&nbsp;</span>
                            <span class="stripe3">&nbsp;</span>
                            <span class="stripe4">&nbsp;</span>
                            <span class="stripe5">&nbsp;</span>
                            <span class="stripe6">&nbsp;</span>

                            <div class="clear">
                                &nbsp;
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.navwrap -->

<div class="clear"></div>

<!-- Nav wrapper -->
<!-- Logo wrapper -->
<div id="headerwrap">
	
	<div class="container center">
		
		<div class="row">
				
			<div class="inner">
			
				<div id="logo" class="grid_four zenleft">
				
					<span>
					  <a href="<?php echo home_url(); ?>" id="logo" itemscope itemtype="http://schema.org/Organization">
						<img src="<?php echo get_template_directory_uri(); ?>/assets/images/CORD_LOGO.png" alt="<?php bloginfo('name'); ?>" />
					  </a>
					</span>
				
				</div>
								
				<div id="header2"  class="grid_four ">
					
					<div class="moduletable">
					
						<div class="jbmoduleBody">
						
							<div class="custom"  >
								<div id="phnumber">Call for a free estimate<br />1-866-542-0802</div>
								
								<div style="display:none;">
									<a href="https://plus.google.com/109728778387663429919" rel="publisher">Google+</a>
								</div>
							
							</div>
						
						</div>
						
					</div>
				
				</div>
			
				<div id="header4"  class="grid_four zenlast">
					<div class="moduletable">
					
						<div class="jbmoduleBody">
							
							<form role="search" method="get" id="searchform" class="searchform" action="<?php echo home_url( '/' ); ?>">
								
								<div class="search">
									
									<label for="mod-search-searchword"> </label>
									<input id="s" name="s" class="inputbox" type="text" value="Search..."  
									onblur="if (this.value=='') this.value='Search...';" 
									onfocus="if (this.value=='Search...') this.value='';"
									/>
									
									<input type="submit" value="Search" class="button" id="searchsubmit"/>
								
								</div>
								
							</form>
						</div>
					
					</div>
					
				</div>
			
			</div>
			
		</div>
		
	</div>
	
</div>
<!-- Logo wrapper -->
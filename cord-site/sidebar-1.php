<?php if ( is_active_sidebar( 'sidebar1' ) ) : ?>

<!-- Left Column -->
<div id="leftCol" class="grid_four twoL" role="complementary">
	
	<div id="left" class="sidebar">
		
		<div class="moduletable">
		
			<div class="jbmoduleBody">
			
			  <div class="custom">
			
				<div class="moduletable-panelmenu color2 border3">
				
					<div class="moduleTitle">
						<h3><span>Menu</span></h3>
					</div>
				
					<div class="jbmoduleBody">
						
						
						<ul class="menu">
							<li class="item-61 active deeper parent menu1"><span class="separator open">Cord Moving and Storage</span>
							
							<?php wp_nav_menu(array(
									'container' => '',                           // enter '' to remove nav container (just make sure .footer-links in _base.scss isn't wrapping)
									'container_class' => '',         // class of container (should you choose to use it)
									'items_wrap' => '<ul style="display: block;">%3$s</ul>',
									'menu' => __( 'Sidebar "Menu"', 'bonestheme' ),   // nav name
									'menu_class' => '',            // adding custom nav class
									'theme_location' => 'sidebar-nav-1',             // where it's located in the theme
									'before' => '',                                 // before the menu
									'after' => '',                                  // after the menu
									'link_before' => '',                            // before each link
									'link_after' => '',                             // after each link
									'depth' => 0                                   // limit the depth of the nav
								)); 
							?>
							
							</li>
						</ul>
					
					</div>
				
				</div>
			
			  </div>
			
			</div>
		
		</div>
				
		<div class="moduletable">
		
			<div class="jbmoduleBody">
			
				<div class="custom">
					
					<div class="moduletable-panelmenu color2 border3">
					
						<div class="moduleTitle">
							<h3><span>Services</span></h3>
						</div>
						<div class="jbmoduleBody">
							
							<ul class="menu">
								<li class="item-61 active deeper parent menu1"><span class="separator open">Services</span>
								<?php wp_nav_menu(array(
										'container' => '',                       // enter '' to remove nav container (just make sure .footer-links in _base.scss isn't wrapping)
										'container_class' => '',         // class of container (should you choose to use it)
										'items_wrap' => '<ul style="display: block;">%3$s</ul>',
										'menu' => __( 'Sidebar "Services"', 'bonestheme' ),   // nav name
										'menu_class' => '',            // adding custom nav class
										'theme_location' => 'sidebar-nav-2',             // where it's located in the theme
										'before' => '',                                 // before the menu
										'after' => '',                                  // after the menu
										'link_before' => '',                            // before each link
										'link_after' => '',                             // after each link
										'depth' => 0                                   // limit the depth of the nav
									)); 
								?>
								</li>
							</ul>
						
						</div>
					
					</div>
				
				</div>
			
			</div>
		
		</div>
		
		<?php dynamic_sidebar( 'sidebar1' ); ?>

		<?php else : ?>
		
		
	
	</div>
	
</div>
<!-- End Left Column -->

<?php endif; ?>
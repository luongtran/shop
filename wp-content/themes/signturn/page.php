
<?php
    get_header();
    
    // action hook for placing content above #container
    thematic_abovecontainer();
?>
                <?php
                    if(is_page('checkout')){
                        $classPage="page-cart";
                    }elseif(is_page('home')){
                        $classPage = 'home';
                    }else{
                        $classPage = '';
                    }
                ?>
            <div id="main-content" class="<?php echo $classPage;?>">
		<?php	
	            // start the loop
	            while ( have_posts() ) : the_post();
                    // action hook for placing content above #post
	            thematic_abovepost();
	        ?>
    	     		
				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?> > 

                            <?php if(is_page('checkout')):?>
                                    <h1>You Shopping Bag</h1>
                            <?php else:
                            // creating the post header
                            thematic_postheader();
                            endif;
				?>
	                
					<div class="entry-content">
	
						<?php
	                    	the_content();
	                    
	                    	wp_link_pages( "\t\t\t\t\t<div class='page-link'>" . __( 'Pages: ', 'thematic' ), "</div>\n", 'number' );
	                    
	                    	edit_post_link( __( 'Edit', 'thematic' ), "\n\t\t\t\t\t\t" . '<span class="edit-link">' , '</span>' . "\n" );
	                    ?>

					</div><!-- .entry-content -->
					
				</div><!-- #post -->
	
			<?php
				// action hook for inserting content below #post
	        	thematic_belowpost();
	        		        
       			// action hook for calling the comments_template
       			//thematic_comments_template();
        		
	        	// end loop
        		endwhile;
	        
	        ?>
	
			</div><!-- #content -->
			
			<?php 
				// action hook for placing content below #content
				thematic_belowcontent(); 
			?> 
			

<?php 
    // action hook for placing content below #container
    thematic_belowcontainer();

    // calling footer.php
    get_footer();
?>
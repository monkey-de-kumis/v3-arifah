<?php
/*******************************************************************************************************
 * Plugin Name: V3 Arifah
 * Plugin URI: httpp://#
 * Description: Display Recent Post Widget using Thumbnail featured image on list recents post
 * Author: Fandy Fardian
 * Version: 3.1.0
 * Author URI: http://#/
 *
 ************************************************************************************************************/
class V3_Arifah extends WP_Widget {

	public function __construct() {
		$v3_opts = array('classname' => 'v3_recent_posts', 'description' => __( "Recent Post&#8217;s With Featured Image."));
			parent::__construct(false, $name = __('V3 Recents Posts'), $v3_opts);

		$this->alt_option_name = 'v3_widget';	
	}

	public function form( $instance) {
		global $wp_registered_sidebars;

		$categories = get_terms( 'category', 'orderby=count&hide_empty=0' );
		$listStyles	= array(''=>__('Empty'),'list'=>__('List'),'inline'=>__('Inline'),);
		$title     		= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$dspl_post    		= isset( $instance['dspl_post'] ) ? absint( $instance['dspl_post'] ) : 5;
		$dspl_date 		= isset( $instance['dspl_date'] ) ? (bool) $instance['dspl_date'] : false;
        $instance = wp_parse_args((array)$instance, $this->defaults);
       
        ?>
        		<p><label><?php _e('Style');?>:</label>
            	<select style="background-color: white;" class="widefat display-style" id="<?php echo $this->get_field_id('style'); ?>" name="<?php echo $this->get_field_name('style'); ?>">
            	<?php foreach ($listStyles as $id => $style) {
                    if ($id != 'wp_inactive_widgets') {
                        $selected = $instance['style'] == $id ? ' selected="selected"' : '';
                        echo sprintf('<option value="%s"%s>%s</option>', $id, $selected, $style);
                    }
                }?>
            	</select><br>
            	<em>Select to display data model inline or list, about other style will be thinking in next version</em>	
            	</p>	
                <p><label><?php _e('Category');?>:</label>
            	<ul>
                <?php

                foreach ($categories as $idx => $cat) :?>
                <?php $checked= (isset($instance[$cat->slug])) ? 'checked="checked"' : ''; ?>
                	<li><input class="checkbox" type="checkbox" <?php echo $checked; ?> id="<?php echo $this->get_field_id( $cat->slug ); ?>" name="<?php echo $this->get_field_name( $cat->slug ); ?>"
                		style="background-color: white;" class="widefat v3-sidebar-select"/>
                		<?php echo $cat->name; ?></li>
                   
                <?php
                endforeach;

                ?>
                </ul>
            <br/>
            <em>If you check the category, Categories your selected will be exclude on query.</em>
        </p>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'dspl_post' ); ?>"><?php _e( 'number of posts to show:' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'dspl_post' ); ?>" name="<?php echo $this->get_field_name( 'dspl_post' ); ?>" type="text" value="<?php echo $dspl_post; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $dspl_date ); ?> id="<?php echo $this->get_field_id( 'dspl_date' ); ?>" name="<?php echo $this->get_field_name( 'dspl_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'dspl_date' ); ?>"><?php _e( 'Display post date?' ); ?></label></p>
        <?php

	}
	public function update(  $new_instance, $old_instance  ) {
		$instance = $old_instace;
		$categories = get_terms( 'category', 'orderby=count&hide_empty=0' );
		foreach ($categories as $key => $value) {
			if(isset($new_instance[$value->slug])) {
				$instance[$value->slug] = $new_instance[$value->slug];
			}
		}
		$instance['style'] = strip_tags(stripslashes($new_instance['style']));
        $instance['title'] = strip_tags($new_instance['title']);
		$instance['dspl_post'] = (int) $new_instance['dspl_post'];
		$instance['dspl_date'] = isset( $new_instance['dspl_date'] ) ? (bool) $new_instance['dspl_date'] : false;

        return $instance;	

	}

	public function widget($args, $instance) {
		add_filter('dynamic_sidebar_params', array(&$this, 'widget_sidebar_params'));
		
		extract($args, EXTR_SKIP);
		echo $before_widget;
		
		$cache = array();
		$exlCat = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'V3_Arifah', 'widget' );
		}
		$categories = get_terms('category', 'orderby=count&hide_empty=0' );
		
		foreach ($categories as $catkey => $catval) {
			
			if (isset($instance[$catval->slug])){

				$exlCat[]= '-' . $catval->term_id;
			}
			
		}
		if ($instance['style']==='inline') {
			$mainClass 		= "myFitri";
			$featuredThumb 	= "featured-thumb";
			$textClass		= "ovaltine";
		} elseif ($instance['style']==='list') {
			$mainClass 		= "myDarlingFitri";
			$featuredThumb 	= "featured-mini";
			$textClass		= "milo";
		} else {
			/*for now it become list style, i don't know what else, not yet find idea*/


		}
		
		?>
		<div class='v3-frame-widgets'>
		<?php
		ob_start();

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'V3 Widgets' );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$dspl_post = ( ! empty( $instance['dspl_post'] ) ) ? absint( $instance['dspl_post'] ) : 5;
		if ( ! $dspl_post )
			$dspl_post = 5;
		$dspl_date = isset( $instance['dspl_date'] ) ? $instance['dspl_date'] : false;
		//$cat
		/**
		 * Filter the arguments for the Recent Posts widget.
		 *
		 * @since 3.4.0
		 * @see WP_Query::get_posts()
		 *
		 * @param array $args An array of arguments used to retrieve the recent posts.
		 */
		$query_args = array(
			'posts_per_page'      => $dspl_post,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true
		);
		
		$r = new WP_Query( apply_filters( 'widget_posts_args', $query_args ) );

		if ($r->have_posts()) :
		?>
		
		<?php if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
		<div class="<?php echo $mainClass; ?>">
			<ul>
			<?php while ( $r->have_posts() ) : $r->the_post(); ?>
				<li>
					<div class="<?php echo $featuredThumb; ?>"><?php the_post_thumbnail( $featuredThumb );  ?></div>

					<div class="<?php echo $textClass; ?>">
				<?php if($instance['style']==='inline') : ?>
					<?php if ( $dspl_date ) : ?>
						<div class="post-date"><?php echo get_the_date(); ?></div>
					<?php endif; ?>
						<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
				<?php else : ?>
					<div class="getset">
						<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a></div>
						<?php if ( $dspl_date ) : ?>
							<div class="post-date"><?php echo get_the_date(); ?>******</div>
						<?php else : ?>
							<div class="post-date"><b>-____-</b></div>
						<?php endif; ?>
						
				<?php endif; ?>
					</div>				
				</li>
			<?php endwhile; ?>
			</ul>
		</div>
		
		<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'V3_Arifah', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
		?>	
		</div>	
		<?php 
		echo $after_widget;
		remove_filter('dynamic_sidebar_params', array(&$this, 'widget_sidebar_params'));
		//$list_widget = wp_get_sidebars_widgets();
		
		
			
		
	}
}

add_action('init', 'v3_widgets_init');
function v3_widgets_init() {
	register_widget('V3_Arifah');

	if (!is_admin()) {
       $url = plugins_url('v3-arifah');
       wp_enqueue_style('V3_Arifah', $url.'/v3.css');
       
		/*
       if ( $check_widget=='content-widgets' ) {
			wp_enqueue_style('V3_Arifah', $url.'/v3.css');
			echo "suksess sayang";
		} else {
			echo "semelekete weleh2";
		} */
	}
		/**
	 * Fires after all default WordPress widgets have been registered.
	 *
	 * @since 2.2.0
	 */
	do_action( 'widgets_init' );
}
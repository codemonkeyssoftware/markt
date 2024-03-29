<?php
/**
 * Recent Products Widget
 * 
 * @package		WooCommerce
 * @category	Widgets
 * @author		WooThemes
 */

class WooCommerce_Widget_Recent_Products extends WP_Widget {

	/** constructor */
	function WooCommerce_Widget_Recent_Products() {
		$widget_ops = array('classname' => 'widget_recent_entries', 'description' => __( "The most recent products on your site", 'woothemes') );
		parent::WP_Widget('recent-products', __('New Products', 'woothemes'), $widget_ops);
		$this->alt_option_name = 'widget_recent_entries';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {
		$cache = wp_cache_get('widget_recent_products', 'widget');

		if ( !is_array($cache) ) $cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);
		
		$title = apply_filters('widget_title', empty($instance['title']) ? __('New Products', 'woothemes') : $instance['title'], $instance, $this->id_base);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

    $show_variations = $instance['show_variations'] ? '1' : '0';

    $args = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'post_type' => 'product');

    if($show_variations=='0'){
      $args['meta_query'] = array(
			  array(
				  'key' => 'visibility',
				  'value' => array('catalog', 'visible'),
				  'compare' => 'IN'
			  )
		  );
		  $args['parent'] = '0';
    }

		$r = new WP_Query($args);
		
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul class="product_list_widget">
		<?php  while ($r->have_posts()) : $r->the_post(); $_product = &new woocommerce_product(get_the_ID()); ?>
		<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>">
			<?php if (has_post_thumbnail()) the_post_thumbnail('shop_tiny'); else echo '<img src="'.woocommerce::plugin_url().'/assets/images/placeholder.png" alt="Placeholder" width="'.woocommerce::get_var('shop_tiny_w').'px" height="'.woocommerce::get_var('shop_tiny_h').'px" />'; ?>
			<?php if ( get_the_title() ) the_title(); else the_ID(); ?>
		</a> <?php echo $_product->get_price_html(); ?></li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		//wp_reset_postdata();

		endif;

		if (isset($args['widget_id']) && isset($cache[$args['widget_id']])) $cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_products', $cache, 'widget');
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];		
		$instance['show_variations'] = !empty($new_instance['show_variations']) ? 1 : 0;

		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_products']) ) delete_option('widget_recent_products');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_products', 'widget');
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		if ( !isset($instance['number']) || !$number = (int) $instance['number'] )
			$number = 5;

		$show_variations = isset( $instance['show_variations'] ) ? (bool) $instance['show_variations'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woothemes'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of products to show:', 'woothemes'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

    <p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_variations'); ?>" name="<?php echo $this->get_field_name('show_variations'); ?>"<?php checked( $show_variations ); ?> />
		<label for="<?php echo $this->get_field_id('show_variations'); ?>"><?php _e( 'Show hidden product variations', 'woothemes' ); ?></label><br />

<?php
	}
} // class WooCommerce_Widget_Recent_Products

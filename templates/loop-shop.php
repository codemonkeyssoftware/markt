<?php

global $columns, $per_page;

do_action('woocommerce_before_shop_loop');

$loop = 0;

if (!isset($columns) || !$columns) $columns = apply_filters('loop_shop_columns', 4);
if (!isset($per_page) || !$per_page) $per_page = apply_filters('loop_shop_per_page', get_option('posts_per_page'));

if ($per_page > get_option('posts_per_page')) query_posts( array_merge( $wp_query->query, array( 'posts_per_page' => $per_page ) ) );

ob_start();

if (have_posts()) : while (have_posts()) : the_post(); $_product = &new woocommerce_product( $post->ID ); $loop++;
	
	?>
	<li class="product <?php if ($loop%$columns==0) echo 'last'; if (($loop-1)%$columns==0) echo 'first'; ?>">
		
		<?php do_action('woocommerce_before_shop_loop_item'); ?>
		
		<a href="<?php the_permalink(); ?>">
			
			<?php do_action('woocommerce_before_shop_loop_item_title', $post, $_product); ?>
			
			<strong><?php the_title(); ?></strong>
			
			<?php do_action('woocommerce_after_shop_loop_item_title', $post, $_product); ?>
		
		</a>

		<?php do_action('woocommerce_after_shop_loop_item', $post, $_product); ?>
		
	</li><?php 
	
	if ($loop==$per_page) break;
	
endwhile; endif;

if ($loop==0) :

	echo '<p class="info">'.__('No products found which match your selection.', 'woothemes').'</p>'; 
	
else :
	
	$found_posts = ob_get_clean();
	
	echo '<ul class="products">' . $found_posts . '</ul><div class="clear"></div>';
	
endif;

do_action('woocommerce_after_shop_loop');
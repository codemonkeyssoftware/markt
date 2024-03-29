<?php
/**
 * Functions used for custom post types in admin 
 *
 * These functions control columns in admin, and other admin interface bits 
 *
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce
 */
 
/**
 * Custom columns
 **/
 function woocommerce_edit_variation_columns($columns){
	
	$columns = array();
	
	$columns["cb"] = "<input type=\"checkbox\" />";
	$columns["thumb"] = __("Thumb", 'woothemes');
	$columns["id"] = __("ID", 'woothemes');
	$columns["title"] = __("Name", 'woothemes');
	$columns["parent"] = __("Parent Product", 'woothemes');

	return $columns;
}
add_filter('manage_edit-product_variation_columns', 'woocommerce_edit_variation_columns');

function woocommerce_custom_variation_columns($column) {
	global $post;
	$product = &new woocommerce_product($post->ID);

	switch ($column) {
		case "thumb" :
			if (has_post_thumbnail($post->ID)) :
				echo get_the_post_thumbnail($post->ID, 'shop_tiny');
			endif;
		break;
		case "id" :
			echo '#'.$post->ID;
		break;
		case "parent" :
			if ($post->post_parent) :
				$parent = get_post( $post->post_parent );
				echo '#'.$parent->ID.' &mdash; <a href="'.admin_url('post.php?post='.$parent->ID.'&action=edit').'">'.$parent->post_title.'</a>';
			endif;
		break;
	}
}
add_action('manage_product_variation_posts_custom_column', 'woocommerce_custom_variation_columns', 2);

function woocommerce_edit_product_columns($columns){
	
	$columns = array();
	
	$columns["cb"] = "<input type=\"checkbox\" />";
	$columns["thumb"] = __("Thumb", 'woothemes');
	$columns["title"] = __("Name", 'woothemes');
	$columns["product_type"] = __("Type", 'woothemes');
	$columns["sku"] = __("ID/SKU", 'woothemes');
	$columns["product_cat"] = __("Category", 'woothemes');
	$columns["product_tags"] = __("Tags", 'woothemes');
	$columns["visibility"] = __("Visibility", 'woothemes');
	$columns["featured"] = __("Featured", 'woothemes');
	
	if (get_option('woocommerce_manage_stock')=='yes') :
		$columns["is_in_stock"] = __("In Stock?", 'woothemes');
		$columns["inventory"] = __("Inventory", 'woothemes');
	endif;
	
	$columns["price"] = __("Price", 'woothemes');
	
	return $columns;
}
add_filter('manage_edit-product_columns', 'woocommerce_edit_product_columns');

function woocommerce_custom_product_columns($column) {
	global $post;
	$product = &new woocommerce_product($post->ID);

	switch ($column) {
		case "thumb" :
			if (has_post_thumbnail($post->ID)) :
				echo get_the_post_thumbnail($post->ID, 'shop_tiny');
			endif;
		break;
		case "summary" :
			echo $post->post_excerpt;
		break;
		case "price":
			echo $product->get_price_html();	
		break;
		case "product_cat" :
			echo get_the_term_list($post->ID, 'product_cat', '', ', ','');
		break;
		case "product_tags" :
			echo get_the_term_list($post->ID, 'product_tag', '', ', ','');
		break;
		case "sku" :
			if ( $sku = get_post_meta( $post->ID, 'SKU', true )) :
				echo '#'.$post->ID.' - SKU: ' . $sku;	
			else :
				echo '#'.$post->ID;
			endif;
		break;
		case "featured" :
			$url = wp_nonce_url( admin_url('admin-ajax.php?action=woocommerce-feature-product&product_id=' . $post->ID) );
			echo '<a href="'.$url.'" title="'.__('Change', 'woothemes') .'">';
			if ($product->is_featured()) echo '<a href="'.$url.'"><img src="'.woocommerce::plugin_url().'/assets/images/success.gif" alt="yes" />';
			else echo '<img src="'.woocommerce::plugin_url().'/assets/images/success-off.gif" alt="no" />';
			echo '</a>';
		break;
		case "visibility" :
			if ( $this_data = $product->visibility ) :
				echo $this_data;	
			else :
				echo '<span class="na">&ndash;</span>';
			endif;
		break;
		case "is_in_stock" :
			if ( !$product->is_type( 'grouped' ) && $product->is_in_stock() ) echo '<img src="'.woocommerce::plugin_url().'/assets/images/success.gif" alt="yes" />';
			else echo '<span class="na">&ndash;</span>';
		break;
		case "inventory" :
			if ( $product->managing_stock() ) :
				echo $product->stock.' in stock';	
			else :
				echo '<span class="na">&ndash;</span>';
			endif;
		break;
		case "product_type" :
			echo ucwords($product->product_type);
		break;
		case "id" :
			echo '#'.$post->ID;
		break;
	}
}
add_action('manage_product_posts_custom_column', 'woocommerce_custom_product_columns', 2);

function woocommerce_edit_order_columns($columns){
	
	$columns = array();
	
	//$columns["cb"] = "<input type=\"checkbox\" />";
	
	$columns["order_status"] = __("Status", 'woothemes');
	
	$columns["order_title"] = __("Order", 'woothemes');
	
	$columns["customer"] = __("Customer", 'woothemes');
	$columns["billing_address"] = __("Billing Address", 'woothemes');
	$columns["shipping_address"] = __("Shipping Address", 'woothemes');
	
	$columns["billing_and_shipping"] = __("Billing & Shipping", 'woothemes');
	
	$columns["total_cost"] = __("Order Cost", 'woothemes');
	
	return $columns;
}
add_filter('manage_edit-shop_order_columns', 'woocommerce_edit_order_columns');

function woocommerce_custom_order_columns($column) {

	global $post;
	$order = &new woocommerce_order( $post->ID );
	switch ($column) {
		case "order_status" :
			
			echo sprintf( __('<mark class="%s">%s</mark>', 'woothemes'), sanitize_title($order->status), $order->status );
			
		break;
		case "order_title" :
			
			echo '<a href="'.admin_url('post.php?post='.$post->ID.'&action=edit').'">'.sprintf( __('Order #%s', 'woothemes'), $post->ID ).'</a>';
			
			echo '<time title="'.date_i18n('c', strtotime($post->post_date)).'">'.date_i18n('F j, Y, g:i a', strtotime($post->post_date)).'</time>';
			
		break;
		case "customer" :
			
			if ($order->user_id) $user_info = get_userdata($order->user_id);

			?>
			<dl>
				<dt><?php _e('User:', 'woothemes'); ?></dt>
				<dd><?php
					if (isset($user_info) && $user_info) : 
			                    	
		            	echo '<a href="user-edit.php?user_id='.$user_info->ID.'">#'.$user_info->ID.' &ndash; <strong>';
		            	
		            	if ($user_info->first_name || $user_info->last_name) echo $user_info->first_name.' '.$user_info->last_name;
		            	else echo $user_info->display_name;
		            	
		            	echo '</strong></a>';
		
		           	else : 
		           		_e('Guest', 'woothemes'); 
		           	endif;
				?></dd>
        		<?php if ($order->billing_email) : ?><dt><?php _e('Billing Email:', 'woothemes'); ?></dt>
        		<dd><a href="mailto:<?php echo $order->billing_email; ?>"><?php echo $order->billing_email; ?></a></dd><?php endif; ?>
        		<?php if ($order->billing_phone) : ?><dt><?php _e('Billing Tel:', 'woothemes'); ?></dt>
        		<dd><?php echo $order->billing_phone; ?></dd><?php endif; ?>
        	</dl>
        	<?php
		break;
		case "billing_address" :
			echo '<strong>'.$order->billing_first_name . ' ' . $order->billing_last_name;
        	if ($order->billing_company) echo ', '.$order->billing_company;
        	echo '</strong><br/>';
        	echo '<a target="_blank" href="http://maps.google.co.uk/maps?&q='.urlencode($order->formatted_billing_address).'&z=16">'.$order->formatted_billing_address.'</a>';
		break;
		case "shipping_address" :
			if ($order->formatted_shipping_address) :
            	echo '<strong>'.$order->shipping_first_name . ' ' . $order->shipping_last_name;
            	if ($order->shipping_company) : echo ', '.$order->shipping_company; endif;
            	echo '</strong><br/>';
            	echo '<a target="_blank" href="http://maps.google.co.uk/maps?&q='.urlencode($order->formatted_shipping_address).'&z=16">'.$order->formatted_shipping_address.'</a>';
        	else :
        		echo '&ndash;';
        	endif;
		break;
		case "billing_and_shipping" :
			?>
			<dl>
				<dt><?php _e('Payment:', 'woothemes'); ?></dt>
				<dd><?php echo $order->payment_method; ?></dd>
        		<dt><?php _e('Shipping:', 'woothemes'); ?></dt>
				<dd><?php echo $order->shipping_method; ?></dd>
        	</dl>
        	<?php
		break;
		case "total_cost" :
			?>
			<table cellpadding="0" cellspacing="0" class="cost">
        		<tr>
        			<th><?php _e('Subtotal', 'woothemes'); ?></th>
        			<td><?php echo woocommerce_price($order->order_subtotal); ?></td>
        		</tr>
        		<?php if ($order->order_shipping>0) : ?><tr>
        			<th><?php _e('Shipping', 'woothemes'); ?></th>
        			<td><?php echo woocommerce_price($order->order_shipping); ?></td>
        		</tr><?php endif; ?>
        		<?php if ($order->get_total_tax()>0) : ?><tr>
        			<th><?php _e('Tax', 'woothemes'); ?></th>
        			<td><?php echo woocommerce_price($order->get_total_tax()); ?></td>
        		</tr><?php endif; ?>
        		<?php if ($order->order_discount>0) : ?><tr>
        			<th><?php _e('Discount', 'woothemes'); ?></th>
        			<td><?php echo woocommerce_price($order->order_discount); ?></td>
        		</tr><?php endif; ?>
        		<tr>	
        			<th><?php _e('Total', 'woothemes'); ?></th>
        			<td><?php echo woocommerce_price($order->order_total); ?></td>
        		</tr>
            </table>
            <?php
		break;
	}
}
add_action('manage_shop_order_posts_custom_column', 'woocommerce_custom_order_columns', 2);

/**
 * Order page filters
 **/
function woocommerce_custom_order_views( $views ) {
	
	$woocommerce_orders = &new woocommerce_orders();
	
	$pending = (isset($_GET['shop_order_status']) && $_GET['shop_order_status']=='pending') ? 'current' : '';
	$onhold = (isset($_GET['shop_order_status']) && $_GET['shop_order_status']=='on-hold') ? 'current' : '';
	$processing = (isset($_GET['shop_order_status']) && $_GET['shop_order_status']=='processing') ? 'current' : '';
	$completed = (isset($_GET['shop_order_status']) && $_GET['shop_order_status']=='completed') ? 'current' : '';
	$cancelled = (isset($_GET['shop_order_status']) && $_GET['shop_order_status']=='cancelled') ? 'current' : '';
	$refunded = (isset($_GET['shop_order_status']) && $_GET['shop_order_status']=='refunded') ? 'current' : '';
	
	$views['pending'] = '<a class="'.$pending.'" href="?post_type=shop_order&amp;shop_order_status=pending">Pending <span class="count">('.$woocommerce_orders->pending_count.')</span></a>';
	$views['onhold'] = '<a class="'.$onhold.'" href="?post_type=shop_order&amp;shop_order_status=on-hold">On-Hold <span class="count">('.$woocommerce_orders->on_hold_count.')</span></a>';
	$views['processing'] = '<a class="'.$processing.'" href="?post_type=shop_order&amp;shop_order_status=processing">Processing <span class="count">('.$woocommerce_orders->processing_count.')</span></a>';
	$views['completed'] = '<a class="'.$completed.'" href="?post_type=shop_order&amp;shop_order_status=completed">Completed <span class="count">('.$woocommerce_orders->completed_count.')</span></a>';
	$views['cancelled'] = '<a class="'.$cancelled.'" href="?post_type=shop_order&amp;shop_order_status=cancelled">Cancelled <span class="count">('.$woocommerce_orders->cancelled_count.')</span></a>';
	$views['refunded'] = '<a class="'.$refunded.'" href="?post_type=shop_order&amp;shop_order_status=refunded">Refunded <span class="count">('.$woocommerce_orders->refunded_count.')</span></a>';
	
	if ($pending || $onhold || $processing || $completed || $cancelled || $refunded) :
		
		$views['all'] = str_replace('current', '', $views['all']);
		
	endif;	

	unset($views['publish']);
	
	if (isset($views['trash'])) :
		$trash = $views['trash'];
		unset($views['draft']);
		unset($views['trash']);
		$views['trash'] = $trash;
	endif;
	
	return $views;
}
add_filter('views_edit-shop_order', 'woocommerce_custom_order_views');

/**
 * Order page actions
 **/
function woocommerce_remove_row_actions( $actions ) {
    if( get_post_type() === 'shop_order' ) :
        unset( $actions['view'] );
        unset( $actions['inline hide-if-no-js'] );
    endif;
    return $actions;
}
add_filter( 'post_row_actions', 'woocommerce_remove_row_actions', 10, 1 );


/**
 * Order page views
 **/
function woocommerce_bulk_actions( $actions ) {
	return array();
}
add_filter( 'bulk_actions-edit-shop_order', 'woocommerce_bulk_actions' );

/**
 * Order messages
 **/
function woocommerce_post_updated_messages( $messages ) {
	if( get_post_type() === 'shop_order' ) :
    	
    	$messages['post'][1] = sprintf( __('Order updated.', 'woothemes') );
    	$messages['post'][4] = sprintf( __('Order updated.', 'woothemes') );
		$messages['post'][6] = sprintf( __('Order published.', 'woothemes') );
		
		$messages['post'][8] = sprintf( __('Order submitted.', 'woothemes') );
		$messages['post'][10] = sprintf( __('Order draft updated.', 'woothemes') );
	
   	endif;
    return $messages;
}
add_filter( 'post_updated_messages', 'woocommerce_post_updated_messages' );


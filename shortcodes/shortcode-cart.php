<?php
/**
 * Cart Shortcode
 * 
 * Used on the cart page, the cart shortcode displays the cart contents and interface for coupon codes and other cart bits and pieces.
 *
 * @package		WooCommerce
 * @category	Shortcode
 * @author		WooThemes
 */
 
function get_woocommerce_cart( $atts ) {
	return woocommerce::shortcode_wrapper('woocommerce_cart', $atts);
}

function woocommerce_cart( $atts ) {
	
	$errors = array();
	
	// Process Discount Codes
	if (isset($_POST['apply_coupon']) && $_POST['apply_coupon'] && woocommerce::verify_nonce('cart')) :
	
		$coupon_code = stripslashes(trim($_POST['coupon_code']));
		woocommerce_cart::add_discount($coupon_code);

	// Update Shipping
	elseif (isset($_POST['calc_shipping']) && $_POST['calc_shipping'] && woocommerce::verify_nonce('cart')) :

		unset($_SESSION['_chosen_method_id']);
		$country 	= $_POST['calc_shipping_country'];
		$state 		= $_POST['calc_shipping_state'];
		
		$postcode 	= $_POST['calc_shipping_postcode'];
		
		if ($postcode && !woocommerce_validation::is_postcode( $postcode, $country )) : 
			woocommerce::add_error( __('Please enter a valid postcode/ZIP.', 'woothemes') ); 
			$postcode = '';
		elseif ($postcode) :
			$postcode = woocommerce_validation::format_postcode( $postcode, $country );
		endif;
		
		if ($country) :
		
			// Update customer location
			woocommerce_customer::set_location( $country, $state, $postcode );
			woocommerce_customer::set_shipping_location( $country, $state, $postcode );
			
			// Re-calc price
			woocommerce_cart::calculate_totals();
			
			woocommerce::add_message(  __('Shipping costs updated.', 'woothemes') );
		
		else :
		
			woocommerce_customer::set_shipping_location( '', '', '' );
			
			woocommerce::add_message(  __('Shipping costs updated.', 'woothemes') );
			
		endif;
			
	endif;
	
	$result = woocommerce_cart::check_cart_item_stock();
	if (is_wp_error($result)) :
		woocommerce::add_error( $result->get_error_message() );
	endif;
	
	woocommerce::show_messages();
	
	if (sizeof(woocommerce_cart::$cart_contents)==0) :
		echo '<p>'.__('Your cart is empty.', 'woothemes').'</p>';
		return;
	endif;
	
	?>
	<form action="<?php echo woocommerce_cart::get_cart_url(); ?>" method="post">
	<table class="shop_table cart" cellspacing="0">
		<thead>
			<tr>
				<th class="product-remove"></th>
				<th class="product-thumbnail"></th>
				<th class="product-name"><span class="nobr"><?php _e('Product Name', 'woothemes'); ?></span></th>
				<th class="product-price"><span class="nobr"><?php _e('Unit Price', 'woothemes'); ?></span></th>
				<th class="product-quantity"><?php _e('Quantity', 'woothemes'); ?></th>
				<th class="product-subtotal"><?php _e('Price', 'woothemes'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (sizeof(woocommerce_cart::$cart_contents)>0) : 
				foreach (woocommerce_cart::$cart_contents as $cart_item_key => $values) :
					$_product = $values['data'];
					if ($_product->exists() && $values['quantity']>0) :
						echo '
							<tr>
								<td class="product-remove"><a href="'.woocommerce_cart::get_remove_url($cart_item_key).'" class="remove" title="Remove this item">&times;</a></td>
								<td class="product-thumbnail"><a href="'.get_permalink($values['product_id']).'">';
						
						if ($values['variation_id'] && has_post_thumbnail($values['variation_id'])) echo get_the_post_thumbnail($values['variation_id'], 'shop_tiny'); 
						elseif (has_post_thumbnail($values['product_id'])) echo get_the_post_thumbnail($values['product_id'], 'shop_tiny'); 
						else echo '<img src="'.woocommerce::plugin_url(). '/assets/images/placeholder.png" alt="Placeholder" width="'.woocommerce::get_var('shop_tiny_w').'" height="'.woocommerce::get_var('shop_tiny_h').'" />'; 
							
						echo '	</a></td>
								<td class="product-name">
									<a href="'.get_permalink($values['product_id']).'">' . apply_filters('woocommerce_cart_product_title', $_product->get_title(), $_product) . '</a>
									'.woocommerce_get_formatted_variation( $values['variation'] ).'
								</td>
								<td class="product-price">'.woocommerce_price($_product->get_price()).'</td>
								<td class="product-quantity"><div class="quantity"><input name="cart['.$cart_item_key.'][qty]" value="'.$values['quantity'].'" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div></td>
								<td class="product-subtotal">'.woocommerce_price($_product->get_price()*$values['quantity']).'</td>
							</tr>';
					endif;
				endforeach; 
			endif;
			
			do_action( 'woocommerce_shop_table_cart' );
			?>
			<tr>
				<td colspan="6" class="actions">
					<div class="coupon">
						<label for="coupon_code"><?php _e('Coupon', 'woothemes'); ?>:</label> <input name="coupon_code" class="input-text" id="coupon_code" value="" /> <input type="submit" class="button" name="apply_coupon" value="<?php _e('Apply Coupon', 'woothemes'); ?>" />
					</div>
					<?php woocommerce::nonce_field('cart') ?>
					<input type="submit" class="button" name="update_cart" value="<?php _e('Update Shopping Cart', 'woothemes'); ?>" /> <a href="<?php echo woocommerce_cart::get_checkout_url(); ?>" class="checkout-button button-alt"><?php _e('Proceed to Checkout &rarr;', 'woothemes'); ?></a>
				</td>
			</tr>
		</tbody>
	</table>
	</form>
	<div class="cart-collaterals">
		
		<?php do_action('cart-collaterals'); ?>

		<div class="cart_totals">
		<?php
		// Hide totals if customer has set location and there are no methods going there
		$available_methods = woocommerce_shipping::get_available_shipping_methods();
		if ($available_methods || !woocommerce_customer::get_shipping_country() || !woocommerce_shipping::$enabled ) : 
			?>
			<h2><?php _e('Cart Totals', 'woothemes'); ?></h2>
			<table cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<th><?php _e('Subtotal', 'woothemes'); ?></th>
						<td><?php echo woocommerce_cart::get_cart_subtotal(); ?></td>
					</tr>
					
					<?php if (woocommerce_cart::get_cart_shipping_total()) : ?><tr>
						<th><?php _e('Shipping', 'woothemes'); ?> <small><?php echo woocommerce_countries::shipping_to_prefix().' '.woocommerce_countries::$countries[ woocommerce_customer::get_shipping_country() ]; ?></small></th>
						<td><?php echo woocommerce_cart::get_cart_shipping_total(); ?> <small><?php echo woocommerce_cart::get_cart_shipping_title(); ?></small></td>
					</tr><?php endif; ?>
					<?php if (woocommerce_cart::get_cart_tax()) : ?><tr>
						<th><?php _e('Tax', 'woothemes'); ?> <?php if (woocommerce_customer::is_customer_outside_base()) : ?><small><?php echo sprintf(__('estimated for %s', 'woothemes'), woocommerce_countries::estimated_for_prefix() . woocommerce_countries::$countries[ woocommerce_countries::get_base_country() ] ); ?></small><?php endif; ?></th>
						<td><?php 
							echo woocommerce_cart::get_cart_tax(); 
						?></td>
					</tr><?php endif; ?>
					
					<?php if (woocommerce_cart::get_total_discount()) : ?><tr class="discount">
						<th><?php _e('Discount', 'woothemes'); ?></th>
						<td>-<?php echo woocommerce_cart::get_total_discount(); ?></td>
					</tr><?php endif; ?>
					<tr>
						<th><strong><?php _e('Total', 'woothemes'); ?></strong></th>
						<td><strong><?php echo woocommerce_cart::get_total(); ?></strong></td>
					</tr>
				</tbody>
			</table>

			<?php
			else :
				echo '<p>'.__('Sorry, it seems that there are no available shipping methods to your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woothemes').'</p>';
			endif;
		?>
		</div>
		
		<?php woocommerce_shipping_calculator(); ?>
		
	</div>
	<?php		
}
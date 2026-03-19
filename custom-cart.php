<?php
if ( class_exists( 'WooCommerce' ) && class_exists( 'WeDevs_Dokan' ) ) {
	
    $cart = WC()->cart->get_cart();
    $cart_items = array();
	
    foreach ( $cart as $cart_item_key => $cart_item )
	{
		//vd($cart_item['unique_key'], '$cart_item[unique_key]');
		vd($cart_item, '$cart_item');
		$attendee_name = '';
		if(!empty($cart_item['unique_key']))
		{
			$args = array(
			    'post_type'  => 'etn-attendee',
			    'meta_query' => array(
			        array(
			            'key'   => 'etn_unique_key',
			            'value' => $cart_item['unique_key'],
			            'compare' => '='
			        )
			    ),
			    'posts_per_page' => -1
			);
		}
		elseif(!empty($cart_item['cart_item_key']))
		{
			$args = array(
			    'post_type'  => 'etn-attendee',
			    'meta_query' => array(
			        array(
			            'key'   => 'cart_item_key',
			            'value' => $cart_item_key,
			            'compare' => '='
			        )
			    ),
			    'posts_per_page' => -1
			);
		}
		
		$attendee_posts = get_posts($args);
		$attendee_names = [];
		
		if($attendee_posts)
		{
			foreach($attendee_posts as $attendee_post)
				$attendee_names[] = $attendee_post->post_title;
			
			$attendee_name = implode(', ', $attendee_names);
		}
		
        $product = $cart_item['data'];
        $vendor_id = get_post_field( 'post_author', $product->get_id() );
		
		$vendor_name = get_user_meta($vendor_id, 'facility_name', TRUE);
		
		if(!$vendor_name)
		{
	        $vendor = get_user_by('ID', $vendor_id);
	        $vendor_name = $vendor->display_name;
		}
		
		$event_id = $product->get_id();
		$start_date = get_post_meta($event_id, 'etn_start_date', true);
		$start_time = get_post_meta($event_id, 'etn_start_time', true);
		$timestamp = strtotime($start_date . ' ' . $start_time);
		$formatted_date = date('M d, Y h:i A', $timestamp);
		
        $cart_items[] = array(
            'product_id'   => $event_id,
            'date_time'    => $formatted_date,
            'product_name' => $product->get_name(),
            'quantity'     => $cart_item['quantity'],
            'price'        => $product->get_price(),
            'subtotal'     => $cart_item['line_subtotal'],
            'total'        => $cart_item['line_total'],
            'vendor_id'    => $vendor_id,
            'vendor_name'  => $vendor_name,
            'attendee_name' => $attendee_name,
        );
    }
	
    $cart_data_json = json_encode( $cart_items );
    echo '<script>var cartData = ' . $cart_data_json . ';</script>';
}
?>
<script>
if (typeof cartData !== 'undefined') {
    console.log('Cart Data:', cartData);
    cartData.forEach(function(item) {
        console.log('Product:', item.product_name, 'product_id:', item.product_id, 'Quantity:', item.quantity, 'Vendor:', item.vendor_name);
    });
}

function customize_cart()
{
	customize_cart_calls++;
	var rows = document.querySelectorAll('.wc-block-cart-items__row');
	
	if(customize_cart_calls > 10)
	{
		clearInterval(customize_cart_timer);
		customize_cart_timer = 0;
	}
	
	if(!rows)
		return;
	
	if(customize_cart_timer)
	{
		clearInterval(customize_cart_timer);
		customize_cart_timer = 0;
	}
	
	if(typeof cartData === 'undefined')
		return;
	
	rows.forEach(function(row) {
	    var nameSpan = row.querySelector('.wc-block-components-product-details__name');
	    var valueSpan = row.querySelector('.wc-block-components-product-details__value');
	    var product_name = row.querySelector('.wc-block-components-product-name');
		var ulElement = row.querySelector('.wc-block-components-product-details')
		
	    if (nameSpan && valueSpan) {
	        //nameSpan.textContent = "Facility/Coach";
	        nameSpan.textContent = "";
	        var rowIndex = Array.from(document.querySelectorAll('.wc-block-cart-items__row')).indexOf(row);
	        if (product_name && cartData[rowIndex]) {
	            valueSpan.textContent = cartData[rowIndex].vendor_name;
				product_name.textContent += ' ' + cartData[rowIndex].date_time;
	        }
			
		    var newLi = document.createElement('li');
		    newLi.className = 'wc-block-components-product-details__vendor';
		
		    var nameSpan = document.createElement('span');
		    nameSpan.className = 'wc-block-components-product-details__name';
		    //nameSpan.textContent = 'Player';
		
		    var valueSpan = document.createElement('span');
		    valueSpan.className = 'wc-block-components-product-details__value';
		    valueSpan.textContent = ' ' + cartData[rowIndex].attendee_name;
		
		    newLi.appendChild(nameSpan);
		    newLi.appendChild(valueSpan);
		
		    ulElement.appendChild(newLi);
	    }
	});
}

var customize_cart_timer = 0;
var customize_cart_calls = 0;

document.addEventListener('DOMContentLoaded', function() {
	customize_cart_timer = setInterval(customize_cart, 1000);
	
});
</script>

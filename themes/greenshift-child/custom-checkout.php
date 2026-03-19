<?php
if ( class_exists( 'WooCommerce' ) && class_exists( 'WeDevs_Dokan' ) ) {
	
    $cart = WC()->cart->get_cart();
    $cart_items = array();
	
    foreach ( $cart as $cart_item_key => $cart_item )
	{
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
			
			$attendee_posts = get_posts($args);
			$attendee_names = [];
			
			if($attendee_posts)
			{
				foreach($attendee_posts as $attendee_post)
					$attendee_names[] = $attendee_post->post_title;
				
				$attendee_name = implode(', ', $attendee_names);
			}
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
<style>
.required {
	visibility: visible!important;
	color:initial!important;
}
</style>
<script>
if (typeof cartData !== 'undefined') {
    console.log('Cart Data:', cartData);
    cartData.forEach(function(item) {
		console.log('item');
		console.log(item);
        console.log('Product:', item.product_name, 'Quantity:', item.quantity, 'Vendor:', item.vendor_name);
    });
}

function customize_cart()
{
	console.log('customize_cart()');
	customize_cart_calls--;
	var rows = document.querySelectorAll('.variation');
	
	if (typeof window.parent.update_system_message !== 'undefined')
		window.parent.update_system_message('Processing checkout... ' + customize_cart_calls, 0);
	
	if(customize_cart_calls <= 0)
	{
		clearInterval(customize_cart_timer);
		customize_cart_timer = 0;
<?php
if(!empty($_GET['do_purchase']))
{
?>
		relogin_ajax();

<?php
}
?>
	}
	
	if(!rows)
		return;
	if(typeof cartData === 'undefined')
		return;
	
	rows.forEach(function(row) {
	    var nameSpan = row.querySelector('dt.variation-Vendor');
	    var valueSpan = row.querySelector('dd.variation-Vendor');
	    //var product_name = row.querySelector('.wc-block-components-product-name');
		var ulElement = nameSpan.parentNode;
		
	    if (nameSpan && valueSpan) {
	        nameSpan.textContent = "Facility/Coach:";
	        var variation_parent = row.querySelector('dd.variation-parent');
			if(variation_parent)
				return;
	        var rowIndex = Array.from(document.querySelectorAll('.variation')).indexOf(row);
	        if (cartData[rowIndex]) {
	            valueSpan.textContent = cartData[rowIndex].vendor_name;
				
			    var valueSpan = document.createElement('dd');
			    valueSpan.className = 'variation-Vendor variation-parent';
			    valueSpan.textContent = cartData[rowIndex].attendee_name;
				ulElement.appendChild(valueSpan);
	        }
	    }
	});
	
<?php
if(!empty($_GET['do_purchase']))
{
?>
	var radio_token = document.querySelector('.woocommerce-SavedPaymentMethods-token input');
	
	console.log('radio_token');
	console.log(radio_token);
	
	if(radio_token)
		radio_token.click();

	var place_order = document.getElementById('place_order');
	if(place_order)
	{
		place_order.click();
	}
<?php
}
?>
	
	var counter = 0;
}

	function relogin_ajax()
	{
		jQuery.ajax({
			type: 'POST',
			dataType: 'html',
			url: '<?=get_stylesheet_directory_uri()?>/relogin.php',
			success: function (data)
			{
				if(data)
				{
					var response = JSON.parse(data);
					console.dir('response!');
					console.dir(response);
					
					if(response.success)
					{
						if(response.message)
						{
							if(typeof window.parent.show_system_message !== 'undefined')
								window.parent.show_system_message(
													'Something went wrong',
													'Please ask the client to check credit card details in the profile',
													'error',
													'Ok',
													'.',
													'',
													''
							);
						}
						else
						{
							if(typeof window.parent.show_system_message !== 'undefined')
								window.parent.show_system_message(
													'Something went wrong',
													'Please ask the client to check credit card details in the profile',
													'error',
													'Ok',
													'.',
													'',
													''
							);
						}
					}
					else
					{
						if(response.message)
						{
							show_system_message(
												'Error', 
												response.message, 
												'error',
												'', 
												'',
												'Close',
												''
							);
						}
						else
						{
						}
					}
				}
				else
				{	
				}
			},
			error : function (jqXHR, textStatus, errorThrown) {
				alert(errorThrown);
				console.log(jqXHR);
			},
		});
		
		return false;
	}


var customize_cart_timer = 0;
var customize_cart_calls = 16;

document.addEventListener('DOMContentLoaded', function() {
	customize_cart_timer = setInterval(customize_cart, 2000);
	
});
</script>

<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

	$stripe_connect_settings = get_option( 'woocommerce_dokan-stripe-connect_settings' );
	if(!empty($stripe_connect_settings['testmode']) && $stripe_connect_settings['testmode'] === 'yes')
		define('STRIPE_TEST_MODE', TRUE);
	else
		define('STRIPE_TEST_MODE', FALSE);
	vd($stripe_connect_settings['testmode'], '$stripe_connect_settings');
*/
	//vd(STRIPE_TEST_MODE, 'STRIPE_TEST_MODE');

	$total_posts = 0;
	$logged_user_id = get_current_user_id();
	$theme_uri = get_stylesheet_directory_uri();
	//$events_count = is_mobile() ? 6 : 12;
	$events_count = 20;
	//$events_count = 1;
	
    $atts = shortcode_atts(array(
		'events_count' => $events_count,
		'all' => FALSE,
		'actions' => TRUE,
		'upcoming' => FALSE,
		'show_filters' => FALSE,
		'view_orders' => FALSE,
    ), $atts);
	
	$user_role = '';
	$today = date('Y-m-d');
	
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $offset = ($paged - 1) * $atts['events_count'];
	$post_status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'publish';
	
	$upcoming_post_initial = $upcoming_posts = [];
	$upcoming_ajax = '';
	if(!empty($atts['upcoming']))
	{
		$upcoming_ajax = $atts['upcoming'];
	    $current_user_id = get_current_user_id();
		
	    $args = [
	        'status' => ['wc-completed', 'wc-processing'],
	        'limit'  => -1,
	        'offset'  => 0,
			'orderby' => 'ID',
			'order' => 'DESC',
		    'customer' => $current_user_id,
	    ];
		
		$subscription_products = [];
		
		$event_ids = array(0);
	    $orders = wc_get_orders( $args );
		//vd(count($orders), 'count($orders)');
		foreach($orders as $order)
		{
			$status = $order->get_status();
			vd($status, '$status');
			if($status !== 'completed')
				continue;
			
			$order_id = $order->get_id();
			$items = $order->get_items();
			//vd(count($items), 'count($items) ' . $order_id);
			
			$is_subscription = FALSE;
			$in_array_subscriptions = FALSE;
			foreach($items as $item)
			{
				$subscription_status = 'active';
				$next_payment = '';
			    //$product_id = $item->get_product_id();
				$product_id = get_product_id_by_order_item_id( $item->get_id() );
				
				//if(!$product_id)
					//vd($product_id, 'no product for item ' . $item->get_name());
				/*
				if(in_array($product_id, $subscription_products))
				{
					$is_subscription = $in_array_subscriptions = TRUE;
					break;
				}
				*/
			    $product = wc_get_product( $product_id );
			    /*
				if($product)
					vd($product->get_name(), '$order ' . $order_id . ' $product ' . $product_id);
				else
					vd(0, '$order ' . $order_id . ' $product ' . $product_id);
				*/
			    if($product && $product->is_type('subscription'))
				{
			        $is_subscription = TRUE;
					
					if ( function_exists( 'wcs_get_subscriptions_for_order' ) ) {
					
					    // Get subscriptions related to the order
					    $subscriptions = wcs_get_subscriptions_for_order( $order_id, array( 'subscriptions_per_page' => -1 ) );
					    
					    if ( ! empty( $subscriptions ) ) {
					        foreach ( $subscriptions as $subscription ) {
					            // Ensure the subscription belongs to the specified customer
					            if ( $subscription->get_user_id() == $current_user_id ) {
								
					                // Check if the product is part of the subscription
					                foreach ( $subscription->get_items() as $item ) {
					                    if ( $item->get_product_id() == $product_id ) {
					                        // Get the subscription status
					                        $subscription_status = $status = $subscription->get_status(); // e.g. 'active', 'cancelled', 'on-hold', 'expired'
											$next_payment = $subscription->get_date( 'next_payment' ); // Returns date or false if no next payment
					                        if ( $next_payment ) {
												$next_payment = date_i18n( 'D M, d Y h:i A', strtotime( $next_payment ) );
												$next_payment = '<strong>Next payment:</strong> ' . $next_payment;
					                            //echo "Next payment date: " . date_i18n( 'F j, Y', strtotime( $next_payment ) ) . "<br>";
					                        } else {
					                            //echo "No upcoming payments for this subscription.<br>";
					                        }
					                        //echo "Subscription status for product ID $product_id: $status";
					                        break 2; // Exit both loops
					                    }
					                }
					            }
					        }
					    } else {
	                        $subscription_status = $status = 'none';
					        //echo "No subscriptions found for this order.";
					    }
					}
					
					$subscription_products[] = $product_id;
			        break;
			    }
			}
			
			//vd($is_subscription, '$is_subscription ' . $order_id);
			//vd($subscription_status, '$subscription_status ' . $order_id);
			
			//if($subscription_status != 'active' && $subscription_status != 'pending-cancel')// && $subscription_status != 'on-hold')
			if($subscription_status != 'active' && $subscription_status != 'pending-cancel' && $subscription_status != 'on-hold')
			{
				continue;
			}
			
			if($in_array_subscriptions)
			{
				//continue;
			}
			
			if($is_subscription)
			{
				
			}
			else
			{
				//vd($items, '$items');
				//vd($order_id, '$order_id');
				//$status = $order->get_status();
				//vd($status, '$status');
				if($status !== 'completed')
				{
					//if(!$is_subscription)
						continue;
				}
			}
			
			//vd($order->get_status(), '$order->get_status() ' . $order_id);
			$payment_status = $order->get_status();
			//vd($payment_status, '$payment_status ' . $order->get_id());
			
		    $attendee_args = array(
		        'post_type'      => 'etn-attendee',
		        'post_status'      => 'publish',
		        'posts_per_page' => -1,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'etn_attendee_order_id',
						'value'   => $order_id,
						'compare' => '=',
					),
				),
		    );
			
			$attendees = get_posts($attendee_args);
			//vd($customer_id, '$customer_id');
			
			$attendee_names = [];
			foreach($attendees as $attendee)
				$attendee_names[] = get_the_title($attendee->ID);
			
			$attendee_names_html = implode(', ', $attendee_names);
			
			foreach($items as $item)
			{
				//$product_id = $item->get_product_id();
				$product_id = get_product_id_by_order_item_id( $item->get_id() );
				if($product_id)
				{
					$event_ids[] = $product_id;
					$post = get_post($product_id);
					//vd($post->post_title, '$post->post_title');
					if($post)
					{
						if($post->post_type == 'etn')
						{
							$etn_start_date = get_post_meta($product_id, 'etn_start_date', TRUE);
							if($etn_start_date >= $today)
							{
								$post->etn_start_date = $etn_start_date;
								$etn_start_timestamp = get_post_meta($product_id, 'etn_start_timestamp', TRUE);
								$post->etn_start_timestamp = !empty($etn_start_timestamp) ? $etn_start_timestamp : '0';
								
								$post->attendees = $attendee_names_html;
								//$post->price = $item->get_subtotal();
								$post->price = get_event_price($product_id);
								$post->order_id = $order_id;
								$post->order_status = $status;
								$post->next_payment = $next_payment;
								//vd($post->price, '$post->price');
								
								$upcoming_posts[] = $post;
								
								//vd($post->etn_start_timestamp, '$post->etn_start_timestamp');
							}
						}
						else
						{
							$post->etn_start_date = '0';
							$post->etn_start_timestamp = '0';
							
							$post->attendees = $attendee_names_html;
							//$post->price = $item->get_subtotal();
							$post->price = get_event_price($product_id);
							$post->order_id = $order_id;
							$post->order_status = $status;
							$post->next_payment = $next_payment;
							//vd($post->price, '$post->price');
							$upcoming_posts[] = $post;
						}
					}
				}
			}
		}
	}
	
	//vd($subscription_products, '$subscription_products');
	
	$booked_posts = array();
	$book_data = get_all_post_meta('book_data');
	//$book_data = 0;
	
	//vd($book_data, '$book_data');
	
	if($book_data)
	{
		foreach($book_data as $book_data_item)
		{
			if($book_data_item['meta_value']['user_id'] == $current_user_id)
			{
				//vd($book_data_item, '$book_data_item');
				$is_relevant = FALSE;
				$booked_post = get_post($book_data_item['post_id']);
				if($booked_post)
				{
					$is_relevant = TRUE;
					$etn_start_date = get_post_meta($book_data_item['post_id'], 'etn_start_date', TRUE);
					if($etn_start_date >= $today)
					{
						$attendee_names_html = '';
						$attendee_names = [];
						if(!empty($book_data_item['meta_value']['players']))
						{
							foreach($book_data_item['meta_value']['players'] as $player)
							{
								//vd($player, '$player');
								
								if(!empty($player['charged']))
								{
									continue;
									//vd($player['charged'], 'charged');
									$order = wc_get_order($player['charged']);
									//vd($order, '$order charged ' . $book_data_item['charged']);
									if($order)
									{
										$status = $order->get_status();
										//vd($status, '$status charged ' . $book_data_item['charged']);
										if($status !== 'completed')
										{
											$is_relevant = FALSE;
											continue;
										}
									}
								}
								
								if(isset($player['player']))
									$attendee_names[] = $player['player'];
							}
						}
						/*
						else
						{
							$user_player = get_user_by('ID', $book_data_item['meta_value']['user_id']);
							if($user_player)
								$attendee_names[] = $user_player->display_name;
						}
						*/
						if(!$attendee_names)
							continue;
						
						$attendee_names_html = implode(', ', $attendee_names);
						
						$booked_post->etn_start_date = $etn_start_date;
						$etn_start_timestamp = get_post_meta($book_data_item['post_id'], 'etn_start_timestamp', TRUE);
						$booked_post->etn_start_timestamp = !empty($etn_start_timestamp) ? $etn_start_timestamp : '0';
						
						$booked_post->attendees = $attendee_names_html;
						
						//$post->price = '';
						
						$upcoming_posts[] = $booked_post;
						
						//vd($booked_post->etn_start_timestamp, '$booked_post->etn_start_timestamp');
					}
				}
			}
		}
	}
	
	usort($upcoming_posts, function($a, $b) {
	    return strcmp($a->etn_start_timestamp, $b->etn_start_timestamp);
	});
	
	$posts = $upcoming_posts;
	//vd($args, '$args');
	//vd($posts, '$posts');
	if(!empty($_REQUEST['past']))
	{
?>
<script>
document.querySelector('.wp-block-post-title').querySelector('a').innerHTML = 'Past Events';
</script>
<?php
	}
	
	if(!empty($_REQUEST['status']) && $_REQUEST['status'] === 'trash')
	{
?>
<script>
document.querySelector('.wp-block-post-title').querySelector('a').innerHTML = 'Archived Events';
</script>
<?php
	}
?>
<style>
.next_payment {
	display:block;
}
</style>
<script type="text/javascript">
var paged = 1;
var posts_per_page = <?=$events_count?>;
var total_posts = <?=$total_posts?>;

jQuery(document).ready(function($) {
    var canBeLoaded = true; // this param allows to initiate the AJAX call only if necessary

    jQuery('#load-more').click(function() {
		get_events_ajax();
    });
});

var get_events_loading = 0;

function get_events_ajax()
{
	paged++;
	var post_data = {
		paged: paged,
		posts_per_page: posts_per_page,
		current_user_id: '<?=$current_user_id?>',
		upcoming: '<?=$upcoming_ajax?>',
		location: '<?=$location_ajax?>',
		date_range: '<?=$date_range_ajax?>',
		event_type: '<?=$event_type_ajax?>',
		activity: '<?=$activity_ajax?>',
		view_orders: '<?=$view_orders?>',
	};
	
	jQuery.ajax({
		type: 'POST',
		dataType: 'html',
		url: '<?=$theme_uri?>/events-ajax.php',
		data: post_data,
		beforeSend : function () {
			get_events_loading = 1;
		},
		success: function (data)
		{
			get_events_loading = 0;
			if(data)
			{
				var response = JSON.parse(data);
				console.dir('response');
				console.dir(response);
				
				if(response.success)
				{
					if(typeof response.CONTENT !== 'undefined')
					{
						document.getElementById('posts-container').innerHTML += response.CONTENT;
					}
					
					console.log('total_posts');
					console.log(response.total_posts);
					console.log('remaining');
					var remaining = total_posts - (response.offset + response.total_posts);
					console.log(remaining);
					if(remaining < 1)
						jQuery('#load-more').css('display', 'none');
					console.log('offset');
					console.log(response.offset);
					//console.log('count');
					//console.log(response.count);
				}
				else
				{
					alert('error');
				}
			}
			else
			{
				alert('no data');
			}
		},
		error : function (jqXHR, textStatus, errorThrown) {
			get_events_loading = 0;
			console.log(jqXHR);
		},
	});
	
	return false;
}
</script>
<div class="dashboard-widget events events-page-wrapper">
<?php
$order_details = '[Order #7777] (March 10, 2025)';
$current_year = date('Y');
$current_time = date('h:i A');

$pattern = '/\ ' . $current_year . '\)/';
$replacement = ' ' . $current_year . ' ' . $current_time . ')';
$order_details = preg_replace($pattern, $replacement, $order_details);

vd($current_year, '$current_year');
vd($order_details, '$order_details');
vd($pattern, '$pattern');
vd($replacement, '$replacement');
//echo $order_details;

?>
	<div class="events_grid" id="posts-container">
<?php
	include('event-posts.php');
?>
	</div>
</div>
<?php
		//$total_posts = count_user_posts($current_user_id, 'etn');
		
        $total_pages = ceil($total_posts / $atts['events_count']);
		//vd($total_posts, '$total_posts');
		//vd($total_pages, '$total_pages');
        if ($total_posts > $atts['events_count']) {
?>
<button id="load-more" class="etn-btn etn-btn-primary">Load More</button>
<div class="pagination-container">
<?php
            /*
			echo paginate_links(array(
                'total'   => $total_pages,
                'current' => $paged,
            ));
			*/
?>
</div>
<?php
        }
?>
<?php
/**
 * Plugin Name: Rez - Order Summary Loader
 * Description: Admin page to populate the rez_order_summary table from WooCommerce orders.
 * Version: 1.2.0
 */

defined('ABSPATH') || exit;
/*
if($_SERVER['REMOTE_ADDR'] == '188.163.75.165')
{
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}
*/
/**
 * Create/Update table on activation
 */
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $table = $wpdb->prefix . 'rez_order_summary';
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	
	//wp_rez_order_summary
	
    $sql = "CREATE TABLE $table (
      item_id bigint(20) unsigned NOT NULL PRIMARY KEY,
	  
      order_id bigint(20) unsigned NOT NULL,
      order_status varchar(50) NOT NULL,
      display_status varchar(50) NOT NULL,
	  
      event_id bigint(20) unsigned NOT NULL,
      event_activity bigint(20) NOT NULL,
      event_category bigint(20) NOT NULL,
      product_category bigint(20) NOT NULL,
      product_subcategory bigint(20) NOT NULL,
	  lesson_type varchar(50) NOT NULL,
	  attendee_names_html varchar(256) NOT NULL,
	  
      dokan_vendor_id bigint(20) unsigned DEFAULT NULL,
      vendor_id varchar(50) NOT NULL,
      stripe_url varchar(256) NOT NULL,
      revenue decimal(12,2) NOT NULL DEFAULT 0.00,
	  
      customer_id bigint(20) unsigned DEFAULT NULL,
      customer_link varchar(256) NOT NULL,
	  
      date_purchased date NOT NULL,
      event_name varchar(191) NOT NULL,
      date_of_event date NOT NULL,
	  
      refunds_data LONGTEXT NOT NULL
	  
    ) $charset_collate;";
	
    dbDelta($sql);
});

/**

      KEY idx_order_id (order_id),
      KEY idx_event_id (event_id),
      KEY idx_order_status (order_status),
      KEY idx_vendor_id (vendor_id),
      KEY idx_customer_id (customer_id),
      KEY idx_date_purchased (date_purchased),
      KEY idx_date_of_event (date_of_event),
      KEY idx_event_name (event_name)


 * Core loader: walks ALL orders; inserts only missing (order_id,event_id) pairs
 */
function rez_populate_order_summary_incremental($per_page = 1000, $single_order_id = 0, $vendor_id = 0, $export = false) {
	//echo '<strong>rez_populate_order_summary_incremental</strong><br>';
    global $wpdb;
    $table = $wpdb->prefix . 'rez_order_summary';
	
    $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	
	$activity_types = get_all_activity_types();
	$event_types = get_all_event_types();
	$event_types[26] = 'Monthly Subscription';
	
	if(!empty($_REQUEST['order_id']))
		$single_order_id = $_REQUEST['order_id'];
	
	if(!empty($_REQUEST['vendor_id']))
		$vendor_id = $_REQUEST['vendor_id'];
	
	$queries = [];
	
	$order_ids = [];
	
	$args = [
            'type'		=> 'shop_order',
//            'status'	=> ['wc-completed', 'wc-refunded', 'wc-processing'],
			'limit'		=> $per_page,
			'paged'		=> $page,
            //'limit'		=> -1,
            'return'	=> 'ids',
            'orderby'	=> 'ID',
            'order'		=> 'ASC',
        ];
	
	if($single_order_id)
	{
		$order_ids = [$single_order_id];
	}
	elseif($vendor_id)
	{
		$args['meta_query'] = [
			[
				'key'     => '_dokan_vendor_id',
				'value'   => $vendor_id,
				'compare' => '=',
			],
		];
		
		$order_ids = wc_get_orders($args);
	}
	else
	{
		$order_ids = wc_get_orders($args);
	}
	
	
    $stats = ['page' => $page, 'orders' => 0, 'items' => 0, 'inserted' => 0, 'order_ids' => array(), 'vendor_id' => $vendor_id, 'args' => $args, 'single_order_id' => $single_order_id];
	//vd($order_ids, '$order_ids');
	//$order_ids = wc_get_orders($args);
	//return $order_ids;
	
    //for($j = 0; $j < 10; $j++)
	if($order_ids)
	{
        $stats['order_ids'] = $order_ids;
		
		if(!empty($order_ids))
		{
	        $stats['orders'] += count($order_ids);
			
			$counter = 0;
			
			//vd($order_ids, '$order_ids');
	        foreach ($order_ids as $order_id) {
				//vd($order_id, '$order_id');
	            $order = wc_get_order($order_id);
				//vd($order, '$order!');
	            if(!$order)
					continue;
				
				$gross_total_amount = (float)$order->get_total();
				
				//if(15122 == $order_id)
					//vd($gross_total_amount, '$gross_total_amount');
				//$total_refund = $order->get_total_refunded();
				//$revenue = $gross_total - $total_refund;
				
	            $customer_id    = (int) $order->get_customer_id();
				$customer_link = $customer_name = $customer_display_name = '';
				
				if($customer_id)
				{
				    $customer = get_userdata($customer_id);
				    if($customer)
					{
						$customer_display_name = $customer->display_name;
						$customer_link = '<a href="/edit-profile/' . $customer->ID . '/">' . $customer->display_name . '</a>';
					}
				}
				
				$vendor_id = '';
	            $dokan_vendor_id = $order->get_meta('_dokan_vendor_id');
				//vd($dokan_vendor_id, '$dokan_vendor_id');
				
	            $order_status   = (string) $order->get_status();
				$display_status = rez_get_order_status($order);
				
				$stripe_url = $payment_intent_id = '';
				
				//if($display_status == 'Pending')
				if(0)
				{
					
				}
				else
				{
					if($dokan_vendor_id)
					{
						$vendor_id = get_user_meta($dokan_vendor_id, 'dokan_connected_vendor_id', true);
						
						//vd($vendor_id, '$vendor_id');
						
						if($vendor_id)
						{
							$charge_id_meta_key = '_dokan_stripe_charge_id_' . $dokan_vendor_id;
							$charge_id = $order->get_meta($charge_id_meta_key);
							
							if($charge_id)
							{
							    $stripe_url = "https://dashboard.stripe.com/{$vendor_id}/payments/{$charge_id}";
							    //echo $stripe_url;
							}
						}
					}
					
					if($stripe_url)
					{
						$display_status = '<a target="_blank" href="' . $stripe_url . '">' . $display_status . '</a>';
					}
					else
					{
						$payment_intent_id = $order->get_meta('_stripe_intent_id');
						//vd($payment_intent_id, '$payment_intent_id');
						if($payment_intent_id)
						{
							$display_status = '<a target="_blank" href="https://dashboard.stripe.com/payments/' . $payment_intent_id . '">' . $display_status . '</a>';
						    $stripe_url = 'https://dashboard.stripe.com/payments/' . $payment_intent_id;
						}
					}
					
				}
				
	            $date_purchased = $order->get_date_created()
	                                  ? $order->get_date_created()->date('Y-m-d')  // site tz
	                                  : null;
				
				$order_items = $order->get_items('line_item');
				//vd($order_items, '$order_items');
				
	            foreach ($order_items as $item_id => $item) {
	                //$event_id = (int) $item->get_product_id();
					$event_id = get_product_id_by_order_item_id( $item_id );
					
	                if ($event_id <= 0)
					{
						//vd($order_id, '$order_id');
		                //$event_id = (int) $item->get_product_id();
						//vd($event_id, '$event_id');
						continue;
					}
					
					//vd($event_id, '$event_id');
					
	                $event_name = get_the_title($event_id) ?: $item->get_name();
					
	                // Event date from Eventin; adjust meta key if yours differs
	                $event_ts = (int) get_post_meta($event_id, 'etn_start_timestamp', true);
	                $date_of_event = $event_ts > 0 ? gmdate('Y-m-d', $event_ts) : null;
					
	                // Revenue for this line (excl. tax). Use +get_total_tax() if you want gross.
	                $revenue = (float) $item->get_total();
					//if(3996	== $order_id)
						//vd($revenue, '$revenue');
					
					
					$attendee_names_html = rez_get_order_attendees_for($order_id, $event_id, $customer_display_name);
					//vd($attendee_names_html, '$attendee_names_html');
					$lesson_type = '';
					
					//$category = get_post_term($event_id, 'etn_category');
					
					$event_category = 0;
					$categories = wp_get_post_terms($event_id, 'etn_category');
					if(!empty($categories[0]))
					{
						$event_category = $categories[0]->term_id;
						if($event_category == 31)//Lesson
						{
							$lesson_type = 'Public Group';
							$etn_categories = get_post_meta($event_id, 'etn_categories', TRUE);
							if(!$etn_categories)
							{
								$booked_charged_orders = get_booked_orders($event_id, TRUE);
								$orders_1_to_1_count = has_orders_for_product($event_id, $booked_charged_orders);
								if($orders_1_to_1_count)
									$lesson_type = '1-on-1';
							}
							elseif($etn_categories == 'Lesson Private Group')
								$lesson_type = 'Private Group';
						}
					}
					
					$product_category = $product_subcategory = 0;
					
					$category_data = get_product_category_and_subcategory( $event_id );
					
				    wph_log_data_global(plugin_dir_path(__FILE__) . 'category_data.txt',
				        array(
				            'event_id' => $event_id,
				            'category_data' => $category_data,
				        ), TRUE, TRUE
				    );
					
					if(!empty($category_data['category_id']))
						$product_category = $category_data['category_id'];
					
					if(!empty($category_data['subcategory_id']))
						$product_subcategory = $category_data['subcategory_id'];
					
					//vd($lesson_type, '$lesson_type');
					
					$event_activity = 0;
					$tags = wp_get_post_terms($event_id, 'etn_tags');
					
					if(!empty($tags[0]))
						$event_activity = $tags[0]->term_id;
					
					$refunds = $order->get_refunds();
					$refunds_data = [];
					
					foreach($refunds as $refund)
					{
					    $refund_date = $refund->get_date_created();
						$refund_date_display = $refund_date->date('n/j/y');
						$refund_amount = $refund->get_amount();
						$refund_total = wc_price($refund_amount);
						$refund_status = $refund_amount >= $gross_total_amount ? 'Refund (full)' : 'Partial Refund';
						/*
						if(14298 == $order_id)
						{
							vd($gross_total_amount, '$gross_total_amount');
							vd($refund_total, '$refund_total');
							vd($refund_status, '$refund_status');
						}
						*/
						if(!empty($stripe_url))
							$refund_status = '<a target="_blank" href="' . $stripe_url . '">' . $refund_status . '</a>';
						
						$refunds_data[] = [
												'refund_id' => $refund->ID,
												'refund_date_display' => $refund_date_display,
												'refund_amount' => $refund_amount,
												'refund_total' => $refund_total,
												'refund_status' => $refund_status,
										];
					}
					
					$refunds_data = json_encode($refunds_data);
					
					$exists = (bool) $wpdb->get_var(
					    $wpdb->prepare("SELECT 1 FROM {$table} WHERE item_id = %d LIMIT 1", $item_id)
					);
					
					if(!$exists)
					{
						$prepare = $wpdb->prepare(
		                    "INSERT IGNORE INTO {$table} (
							item_id, order_id, order_status, display_status, 
							event_id, event_activity, event_category, 
							product_category, product_subcategory, 
							lesson_type, attendee_names_html, 
							dokan_vendor_id, vendor_id, stripe_url, revenue, 
							customer_id, customer_link, 
							date_purchased, event_name, date_of_event, 
							refunds_data
							) VALUES (
							%d, %d, %s, %s, 
							%d, %d, %d, 
							%d, %d, 
							%s, %s, 
							%d, %s, %s, %f, 
							%d, %s, 
							%s, %s, %s, 
							%s
							)",
		                    $item_id, $order_id, $order_status, $display_status, 
							$event_id, $event_activity, $event_category, 
							$product_category, $product_subcategory, 
							$lesson_type, $attendee_names_html, 
							$dokan_vendor_id, $vendor_id, $stripe_url, $revenue, 
							$customer_id, $customer_link, 
		                    $date_purchased, $event_name, $date_of_event, 
							$refunds_data
		                );
					}
					else
					{
						$prepare = $wpdb->prepare(
						    "UPDATE {$table} SET
						        order_id            = %d,
						        order_status        = %s,
						        display_status      = %s,
						        event_id            = %d,
						        event_activity      = %d,
						        event_category		= %d,
						        product_category	= %d,
						        product_subcategory	= %d,
						        lesson_type         = %s,
						        attendee_names_html = %s,
						        dokan_vendor_id     = %d,
						        vendor_id           = %s,
						        stripe_url          = %s,
						        revenue             = %f,
						        customer_id         = %d,
						        customer_link       = %s,
						        date_purchased      = %s,
						        event_name          = %s,
						        date_of_event       = %s,
						        refunds_data        = %s
						     WHERE item_id = %d",
						    $order_id, $order_status, $display_status,
						    $event_id, $event_activity, $event_category, $product_category, $product_subcategory, $lesson_type, $attendee_names_html,
						    $dokan_vendor_id, $vendor_id, $stripe_url, $revenue,
						    $customer_id, $customer_link,
						    $date_purchased, $event_name, $date_of_event,
						    $refunds_data,
						    $item_id
						);
					}
					
					if($export)
						$queries[] = rtrim($prepare, ";\n") . ';';
					else
					{
						$res_query = $wpdb->query( $prepare );
					}
					
					//vd($prepare, '$prepare');
					
					    wph_log_data_global(plugin_dir_path(__FILE__) . 'prepare.txt',
					        array(
					            'order_id' => $order_id,
					            'prepare' => $prepare,
					            'res_query' => $res_query,
					        ), TRUE, TRUE
					    );
					
					//$wpdb->query( $sql );
	                $stats['items']++;
					$rows_affected = $wpdb->rows_affected;
	                $stats['inserted'] += $rows_affected;
					//if(3996	== $order_id)
						//vd($prepare, '$prepare');
					//vd($sql, '$sql');
					
					//vd($rows_affected, '$rows_affected');
					//return $prepare;
					//continue;
					
					$counter++;
					//vd($counter, '$counter');
					/*
					if($counter > 2)
						return $stats;
					*/
	            }
	        }
		}
		
        //$page++;
    }
	
	if($export)
	{
		//$export_dir  = plugin_dir_path(__FILE__) . 'exports/';
		//wp_mkdir_p($export_dir);
		$export_dir  = plugin_dir_path(__FILE__) . '/';


		$base_name = 'rez_order_summary';
		$chunk_size = 1000;
		
		$chunks = array_chunk($queries, $chunk_size);
		$idx = 1;
		
		foreach ($chunks as $chunk) {
		    $file = $export_dir . "{$base_name}-{$idx}.sql";
		    $fh = fopen($file, 'wb');
		    if (!$fh) {
		        error_log('Cannot open SQL export file: ' . $file);
		        $idx++;
		        continue;
		    }
		
		    fwrite($fh, "-- {$base_name} export\n");
		    fwrite($fh, "SET NAMES utf8mb4;\nSTART TRANSACTION;\n");
		
		    foreach ($chunk as $q) {
		        fwrite($fh, $q . "\n");
		    }
		
		    fwrite($fh, "COMMIT;\n");
		    fclose($fh);
		    $idx++;
		}
	
	}
	
	$overwrite = $page > 1 ? FALSE : TRUE;
    wph_log_data_global(plugin_dir_path(__FILE__) . 'stats.txt',
        array(
            'stats' => $stats,
        ), TRUE, $overwrite
    );
	
    return $stats;
}

/**
 * Admin page
 */
add_action('admin_menu', function () {
    add_menu_page(
        'Order Summary Loader',
        'Order Summary',
        'manage_woocommerce',
        'rez-order-summary',
        'rez_order_summary_admin_page',
        'dashicons-update',
        58
    );
});

function rez_order_summary_admin_page() {
    if ( ! current_user_can('manage_woocommerce') ) {
        wp_die(__('Insufficient permissions.', 'rez'));
    }

    $ran = false;
    $stats = null;
    $msg = '';
	$page = 0;

    if ( isset($_POST['rez_run']) ) {
        check_admin_referer('rez_run_populate', 'rez_nonce');

        $per_page = isset($_POST['per_page']) ? (int) $_POST['per_page'] : 1000;
        if ($per_page < 50)  $per_page = 50;
        if ($per_page > 2000) $per_page = 2000;

        $ran   = true;
        //$stats = rez_populate_order_summary_incremental($per_page);
        $stats = rez_populate_order_summary_incremental($per_page, $single_order_id = 0, $vendor_id = 0, $export = false);
		
		//vd($stats);
		
		if(!empty($stats['page']))
			$page = $stats['page'];
		
        $msg   = sprintf(
            'Processed %d orders, %d line items; affected %d rows.',
            (int) $stats['orders'],
            (int) $stats['items'],
            (int) $stats['inserted']
        );
    }
	
	$page++;
    ?>
    <div class="wrap">
        <h1>Order Summary Loader</h1>
<?php
	//vd($ran);
?>
        <?php if ($ran): ?>
            <div class="notice notice-success"><p><?php echo esc_html($msg); ?></p></div>
			<?php if(!empty($stats['export_file'])) echo 'export file<br>' . $stats['export_file']; ?>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('rez_run_populate', 'rez_nonce'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="per_page">Batch size</label></th>
                    <td>
                        <input name="per_page" id="per_page" type="number" min="50" max="2000" step="50" value="<?php echo isset($_POST['per_page']) ? (int) $_POST['per_page'] : 1000; ?>" />
                        <p class="description">Orders fetched per batch.</p>
                    </td>
                    <th scope="row"><label for="per_page">Page</label></th>
                    <td>
                        <input name="page" id="page" type="number" value="<?php echo $page; ?>" />
                        <p class="description">Current run</p>
                    </td>
                </tr>
            </table>
            <p>
                <button type="submit" class="button button-primary" name="rez_run" value="1">Run</button>
            </p>
        </form>
    </div>
    <?php
}

function rez_get_order_status($order, $display_statuses = 0, $is_seller = TRUE)
{
    if(!is_a($order, 'WC_Order') && is_numeric($order))
		$order = wc_get_order( $order );
	
	if($order && is_a($order, 'WC_Order'))
	{
		if(!$display_statuses)
			$display_statuses = array(
				'processing' => 'Paid',
				'completed' => 'Paid',
				'cancelled' => 'Uncollectable',
				'failed' => 'Failed',
				
				'refunded' => 'Refund (full)',
				//'refunded' => 'Refund',
				'partial-refund' => 'Partial Refund',
				'pending' => 'Pending',
				'trash' => 'Deleted',
			);
		
		$payment_status = $order->get_status();
		//vd($payment_status, '$payment_status');
		if($payment_status == 'completed')
		{
		    $total_refunded = $order->get_total_refunded();
			
			if($total_refunded)
			{
				if($is_seller)
				{
				    $order_total = $order->get_total();
					$total_fee = wph_calculate_order_fee($order);
				    $remaining_amount = $order_total - $total_refunded;
					if($remaining_amount <= $total_fee)
						$payment_status = 'refunded';
					else
						$payment_status = 'partial-refund';
					/*
					vd($order_total, '$order_total');
					vd($total_refunded, '$total_refunded');
					vd($total_fee, '$total_fee');
					vd($payment_status, '$payment_status ----------- ');
					*/
				}
				else
					$payment_status = 'partial-refund';
			}
		}
		
		$display_status = isset($display_statuses[$payment_status]) ? $display_statuses[$payment_status] : '';
		return $display_status;
	}
	
	return '';
}

function rez_get_order_attendees_for($order_id, $event_id, $display_name)
{
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
			array(
				'key'     => 'etn_event_id',
				'value'   => $event_id,
				'compare' => '=',
			),
		),
    );
	
	$attendees = get_posts($attendee_args);
	$count_attendees = count($attendees);
	$attendee_names = [];
	foreach($attendees as $attendee)
		$attendee_names[] = get_the_title($attendee->ID);
	
	$attendee_names_html = implode(', ', $attendee_names);
	if($attendee_names_html && $display_name !== $attendee_names_html)
		$attendee_names_html = ' (for ' . $attendee_names_html . ')';
	else
		$attendee_names_html = '';
	
	return $attendee_names_html;
}

add_action('plugins_loaded', function () {
    if ( ! class_exists('WooCommerce') ) return;


	add_action('woocommerce_order_refunded', function ($order_id, $refund_id) {
	
        rez_sync_order_to_summary($order_id);
	    wph_log_data_global(plugin_dir_path(__FILE__) . 'woocommerce_order_refunded.txt',
	        array(
	            'order_id' => $order_id,
	            'refund_id' => $refund_id,
	        ), TRUE, TRUE
	    );
	
	}, 10, 2);


    add_action('woocommerce_checkout_order_processed', function ($order_id, $posted_data, $order) {
        rez_sync_order_to_summary($order_id);
	    wph_log_data_global(plugin_dir_path(__FILE__) . 'woocommerce_checkout_order_processed.txt',
	        array(
	            'order_id' => $order_id,
	            'posted_data' => $posted_data,
	        ), TRUE, TRUE
	    );
    }, 10, 3);
	
    add_action('woocommerce_payment_complete', function ($order_id) {
        rez_sync_order_to_summary($order_id);
	    wph_log_data_global(plugin_dir_path(__FILE__) . 'woocommerce_payment_complete.txt',
	        array(
	            'order_id' => $order_id,
	        ), TRUE, TRUE
	    );
    }, 10, 1);
	
	/*
    add_action('woocommerce_store_api_checkout_order_processed', function ($order, $data) {
		$order_id = $order->get_id();
        rez_sync_order_to_summary($order_id);
	    wph_log_data_global(plugin_dir_path(__FILE__) . 'woocommerce_store_api_checkout_order_processed.txt',
	        array(
	            'order_id' => $order_id,
	            'data' => $data,
	        ), TRUE, FALSE
	    );
    }, 10, 2);
	
    add_action('woocommerce_order_status_changed', function ($order_id, $old_status, $new_status) {
        rez_sync_order_to_summary($order_id);
	    wph_log_data_global(plugin_dir_path(__FILE__) . 'woocommerce_order_status_changed.txt',
	        array(
	            'order_id' => $order_id,
	            'old_status' => $old_status,
	            'new_status' => $new_status,
	        ), TRUE, FALSE
	    );
    }, 10, 3);
	*/
});

function rez_sync_order_to_summary($order_id) {
    $stats = rez_populate_order_summary_incremental(1000, $order_id);
    wph_log_data_global(plugin_dir_path(__FILE__) . 'rez_sync_order_to_summary.txt',
        array(
            'order_id' => $order_id,
            'stats' => $stats,
        ), TRUE, TRUE
    );
}

// ---- schedule daily kickoff
register_activation_hook(__FILE__, function () {
    if ( ! wp_next_scheduled('rez_summary_daily_kick') ) {
        wp_schedule_event(time() + 60, 'daily', 'rez_summary_daily_kick');
    }
});

// ---- daily kickoff ? start with page=1
add_action('rez_summary_daily_kick', function () {
    wp_schedule_single_event(time() + 1, 'rez_summary_tick', [1]); // page = 1
});

// ---- one tick: run a page and chain next if needed
add_action('rez_summary_tick', function ( $page ) {
    // optional lock
    if ( get_transient('rez_summary_tick_lock') ) {
        wp_schedule_single_event(time() + 5, 'rez_summary_tick', [ (int)$page ]);
        return;
    }
    set_transient('rez_summary_tick_lock', 1, 60);
	
	$_REQUEST['page'] = $page;
    $stats = rez_populate_order_summary_incremental();
	
    delete_transient('rez_summary_tick_lock');
	
    if ( ! empty($stats['orders']) ) {
        // more to do ? schedule next page immediately
        wp_schedule_single_event(time() + 1, 'rez_summary_tick', [ (int)$page + 1 ]);
    }
}, 10, 1);


function get_product_category_and_subcategory( $product_id )
{
    $result = [
        'category'     => null,
        'subcategory'  => null,
        'category_id'  => null,
        'subcategory_id' => null,
    ];

    $terms = get_the_terms( $product_id, 'product_cat' );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return $result;
    }

    foreach ( $terms as $term ) {

        // We want a subcategory (has parent)
        if ( (int) $term->parent !== 0 ) {

            $parent = get_term( $term->parent, 'product_cat' );

            if ( $parent && ! is_wp_error( $parent ) ) {
                $result['category']        = $parent->name;
                $result['category_id']     = $parent->term_id;
                $result['subcategory']     = $term->name;
                $result['subcategory_id']  = $term->term_id;
                return $result;
            }
        }
    }

    // Fallback: product only has a top-level category
    foreach ( $terms as $term ) {
        if ( (int) $term->parent === 0 ) {
            $result['category']    = $term->name;
            $result['category_id'] = $term->term_id;
            return $result;
        }
    }

    return $result;
}

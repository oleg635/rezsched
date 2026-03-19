<?php
namespace Etn\Core\Admin;

use Etn\Base\Exporter\Post_Exporter;
use Etn\Base\Importer\Post_Importer;
use Etn\Traits\Singleton;
use WP_Error;

require($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph-export-posts.txt',
	array(
		'$_GET' => $_GET,
	), TRUE, TRUE
);

//vd($_GET, '$_GET');
//return;

function wph_trash_posts()
{
    if (!isset($_GET['post'])) {
        return;
    }
	
    $post_ids = explode(',', $_GET['post']);
	
	wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph_trash_posts.txt',
		array(
			'$post_ids' => $post_ids,
		), TRUE, TRUE
	);
	
    foreach ($post_ids as $post_id) {
        $post_id = intval($post_id);
        if ($post_id > 0) {
			$post_type = get_post_type($post_id);
			
			if($post_type == 'shop_order_placehold')
			{
	            //wp_trash_post($post_id);
				//wp_delete_post($post_id, FALSE);
				
				$order = wc_get_order( $post_id );
				
				if($order)
					$order->delete( false );
			}
			else
				wp_delete_post($post_id, FALSE);
        }
    }
}

function wph_trash_orders()
{
    $post_ids = isset($_GET['post']) ? explode(',', sanitize_text_field($_GET['post'])) : array();
	
    if (!empty($post_ids)) {
        foreach ($post_ids as $post_id) {
            $post_id = intval($post_id);
			
            // Check if the post is a WooCommerce order
			$post_type = get_post_type($post_id);
			
			wph_log_data_global($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/greenshift-child/wph_trash_orders.txt',
				array(
					'$post_type' => $post_type,
				), TRUE, FALSE
			);
			
            if ($post_type === 'shop_order') {
                // Change order status to cancelled before trashing
                $order = wc_get_order($post_id);
                if ($order && $order->get_status() !== 'cancelled') {
                    $order->update_status('cancelled', __('Order moved to trash.'));
                }
				
                wp_trash_post($post_id);
            }
        }
    }
}

function wph_export_data()
{
    $action    = isset($_GET['etn-action']) ? sanitize_text_field($_GET['etn-action']) : 'export';
    $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'etn-attendee';
    $format    = isset($_GET['format']) ? sanitize_text_field($_GET['format']) : 'json';
	
	if($format == 'trash')
	{
		wph_trash_posts();
		$redirect_url = wp_get_referer() ? wp_get_referer() : admin_url();
		wp_safe_redirect($redirect_url);
		exit;
	}
	
    if ('export' != $action) {
        return;
    }
	
    $post_ids = isset($_GET['post']) ? explode(',', $_GET['post']) : wph_get_post_ids($post_type);
    $data     = [];
	
	$display_statuses = array(
		'processing' => 'Paid',
		'completed' => 'Paid',
		'failed' => 'Uncollectable',
		'cancelled' => 'Uncollectable',
		'refunded' => 'Refund (full)',
		'partial-refund' => 'Partial Refund',
		'cancelled' => 'Uncollectable',
		'pending' => 'Failed',
		'on-hold' => 'Open',
		'trash' => 'Deleted',
	);
	
    foreach ($post_ids as $order_id) {
		$order = wc_get_order($order_id);
		$order_data = [];
		
		if ($order) {
		    $order_date = $order->get_date_created();
		    $formatted_order_date = $order_date->date('Y-m-d H:i:s');
		    $order_subtotal = $order->get_subtotal();
			$payment_status = $order->get_status();
			$display_status = isset($display_statuses[$payment_status]) ? $display_statuses[$payment_status] : '';
			
			//$order_number = $order->get_order_number();
			$customer_id = $order->get_customer_id();
			$customer_name = $customer_email = '';
			if($customer_id)
			{
				$customer = get_user_by('ID', $customer_id);
				if($customer)
				{
					$customer_name = $customer->display_name;
					$customer_email = $customer->user_email;
				}
			}
			
			$event_names = [];
			$event_name = '';
			
		    foreach ( $order->get_items() as $item_id => $item ) {
		        $product_name = $item->get_name();
				$event_names[] = $product_name;
		    }
			
			$event_name = implode(', ', $event_names);
			
			$order_data['Order Date'] = $formatted_order_date;
			$order_data['Order ID'] = $order_id;
			$order_data['Client'] = $customer_name;
			$order_data['Email'] = $customer_email;
			$order_data['Event'] = $event_name;
			$order_data['Amount'] = $order_subtotal;
			$order_data['Payment Status'] = $display_status;
		}
		
        $data[] = $order_data;
    }
	
    if ($format == 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="export.json"');
        echo json_encode($data);
    } elseif ($format == 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="export.csv"');

        $output = fopen('php://output', 'w');

        // Output column headers
        fputcsv($output, array_keys($data[0]));

        // Output rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
    }
    exit;
}

function wph_export_data_old()
{
    $action    = isset( $_GET['etn-action'] ) ? sanitize_text_field( $_GET['etn-action'] ) : 'export';
    $post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'etn-attendee';
    $format    = isset( $_GET['format'] ) ? sanitize_text_field( $_GET['format'] ) : 'json';
	
    if ( 'export' != $action ) {
        return;
    }
	
    $post_ids      = isset( $_GET['post'] ) ? explode(',', $_GET['post']) : wph_get_post_ids( $post_type );
    $post_exporter = Post_Exporter::get_post_exporter( $post_type );
	
    $post_exporter->export( $post_ids, $format );
}

function wph_get_post_ids( $post_type )
{
    $args = [
        'post_type'   => $post_type,
        'numberposts' => -1,
        'post_status' => 'publish',
        'fields'      => 'ids',
    ];
	
    $posts = get_posts( $args );
	
    return $posts;
}

wph_export_data();
?>
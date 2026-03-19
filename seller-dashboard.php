<?php
function get_completed_orders_count_and_total() {
    global $wpdb;

    $current_user_id = get_current_user_id();
    if ( ! $current_user_id ) {
        return [
            'orders' => [],
            'count'  => 0,
            'total'  => 0,
        ];
    }

    // Query all completed / partially refunded orders for this vendor (HPOS)
    $sql = "
        SELECT orders.id
        FROM {$wpdb->prefix}wc_orders AS orders
        INNER JOIN {$wpdb->prefix}wc_orders_meta AS meta ON orders.id = meta.order_id
        WHERE meta.meta_key = '_dokan_vendor_id'
          AND meta.meta_value = %d
          AND orders.status IN ('wc-completed', 'wc-partial-refund')
        ORDER BY orders.id DESC
    ";

    $order_ids = $wpdb->get_col( $wpdb->prepare( $sql, $current_user_id ) );

    $orders = [];
    $order_total = 0.0;

    if ( ! empty( $order_ids ) ) {
        foreach ( $order_ids as $order_id ) {
            $order = wc_get_order( $order_id );

            // Skip invalid objects or refunds
            if ( ! $order instanceof WC_Order || $order instanceof WC_Order_Refund ) {
                continue;
            }

            $subtotal       = $order->get_subtotal();
            $total_refunded = $order->get_total_refunded();

            if ( $total_refunded ) {
                $subtotal -= $total_refunded;
            }

            $order_total += $subtotal;
            $orders[] = $order;
        }
    }

    return [
        'count'  => count( $orders ),
        'total'  => $order_total,
    ];
}

$result = get_completed_orders_count_and_total();
?>
<style>
.attendees-box {
	padding:0!important;
}

.dokan-dashboard .dokan-dashboard-content article.dashboard-content-area .dashboard-widget.big-counter li.seller-dashboard-buttons {
	border: none;
	background: none;
	padding:0!important;	
    display: flex;
    flex-direction: column;
    height: 100%;
}
.seller-dashboard-buttons #div-add-waiver {
    margin-top: auto;
}
.seller-dashboard-buttons div {
	width:100%;
}
.seller-dashboard-buttons .add_order, .seller-dashboard-buttons .add_waiver {
	width:100%;
}

.seller-dashboard-buttons .add_order:before, .seller-dashboard-buttons .add_waiver:before {
    content: url(https://rezbeta.wpenginepowered.com/wp-content/uploads/2024/04/Add_Plus.svg);
    position: relative;
    top: 5px;
    left: -4px;
}
.seller-dashboard-buttons .add_order, .seller-dashboard-buttons .add_waiver {
    border-radius: var(--Corner-Medium-small, 10px);
    background: var(--Accent-color-100, #17A0B2);
    color: var(--Additional-colors-White, #FFF);
}

.dokan-dashboard .dokan-dashboard-content .events-table a {
	color: #1b1d21;
	text-decoration: underline;
}

.dashboard-content-area .dashboard-widget.big-counter ul.list-inline > li:nth-child(2) .count,
.dashboard-content-area .dashboard-widget.big-counter ul.list-inline > li:nth-child(4) .count
{
    width: 80% !important;
    text-align: left !important;
    margin-left: 30% !important; 
    font-size: 28px !important;
    @media (max-width: 1332px) {
        margin-left: 30% !important; 
        margin-top: 0px !important;
        font-size: 28px !important;
    }
    @media (max-width: 1230px) {
        font-size: 24px !important;
    }
    @media (max-width: 1110px) {
        font-size: 20px !important;
        margin-left: 30% !important; 
    }
    @media (max-width: 1001px) {
        margin-left: 20% !important;   
    } 
}

.dashboard-content-area .dashboard-widget.big-counter ul.list-inline > li:nth-child(2) .title,
.dashboard-content-area .dashboard-widget.big-counter ul.list-inline > li:nth-child(4) .title
{
    width: 80% !important;
    text-align: left;
    margin-left: 30% !important;
    @media (max-width: 1332px) {
        margin-top: 10px !important;
        margin-left: 30% !important;  
    }
    @media (max-width: 1230px) {
        margin-top: 15px !important;
    }
    @media (max-width: 1110px) {
        font-size: 20px !important;
        margin-left: 30% !important; 
    }
    @media (max-width: 1001px) {
        margin-left: 20% !important;  
        margin-top: 0px !important; 
    } 
}
.dashboard-content-area .dashboard-widget.big-counter ul.list-inline > li:nth-child(2),
.dashboard-content-area .dashboard-widget.big-counter ul.list-inline > li:nth-child(4)
{ 
    background-repeat: no-repeat;
    background-position-x: 4%;
    background-position-y: 50%;
    background-size: 25%;
    @media (max-width: 1001px) {
        background-size: 15%; 
    }
}
</style>
<div class="dokan-dashboard-wrap">
	<div class="dokan-dashboard-content">
		<article class="dashboard-content-area">
			<div class="dashboard-widget big-counter">
			    <ul class="list-inline">
					<li style="display:none;">
						<div class="title">Net Sales</div>
						<div class="count"></div>
					</li>
			        <li>
			            <div class="title">Earnings</div>
			            <div class="count"><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">$</span><?=number_format( (float) $result['total'], 2, '.', '' )?></span></div>
			        </li>
					<li style="display:none;">
						<div class="title">Pageview</div>
						<div class="count">0</div>
					</li>
			        <li>
			            <div class="title">Orders</div>
			            <div class="count">
							<?=$result['count']?>
						</div>
			        </li>
					<li class="seller-dashboard-buttons">
						<div id="div-add-order"><a href="/create-event/" class="etn-btn etn-btn-primary add_order">Add Event</a></div>
						<div id="div-add-waiver"><a href="/create-waiver/" class="etn-btn etn-btn-primary add_waiver">Add waiver</a></div>
					</li>
				</ul>
			</div>
		<div><h2>Recent Orders</h2></div>
		<style>
		.events-table-wrapper {
		    margin-top: initial!important;
		}
		.dokan-dashboard .dokan-dashboard-content article.dashboard-content-area .dashboard-widget {
			margin-bottom: initial !important;
		}		
		</style>
		<?=apply_filters('the_content', '[orders_table show_title=0 show_view_all=0]');?>
		</article>
	</div>
</div>

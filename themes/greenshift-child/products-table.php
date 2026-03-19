<?php
if($_SERVER['REMOTE_ADDR'] == '188.163.74.62')
{
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
}

$logged_user_id = get_current_user_id();

$simulate_checkout_vendor = get_user_meta($logged_user_id, 'simulate_checkout_vendor', TRUE);
if($simulate_checkout_vendor)
{
	delete_user_meta($logged_user_id, 'simulate_checkout_vendor');
	wp_set_current_user($simulate_checkout_vendor);
	wp_set_auth_cookie($simulate_checkout_vendor);
	
?>
<script>
	window.location.reload();
</script>
<?php
	return;
}

function rez_get_product_subcategories_by_parents( array $parent_slugs ) {

    if ( empty( $parent_slugs ) ) {
        return [];
    }

    $parent_ids = [];

    foreach ( $parent_slugs as $slug ) {
        $term = get_term_by( 'slug', $slug, 'product_cat' );
        if ( $term && $term->parent === 0 ) {
            $parent_ids[] = (int) $term->term_id;
        }
    }

    if ( empty( $parent_ids ) ) {
        return [];
    }

    $children = get_terms( [
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ] );

    $result = [];
    $seen   = [];

    foreach ( $children as $term ) {
        if ( in_array( (int) $term->parent, $parent_ids, true ) ) {

            if ( isset( $seen[ $term->slug ] ) ) {
                continue;
            }

            $seen[ $term->slug ] = true;
            $result[] = $term;
        }
    }

    return $result;
}

function rez_get_top_level_product_category_slug( $product_id ) {

    $terms = get_the_terms( $product_id, 'product_cat' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return '';
    }

    foreach ( $terms as $term ) {
        if ( $term->parent === 0 ) {
            return $term->slug;
        }
    }

    return '';
}

function rez_sync_product_sort_metas( int $product_id ): void {

    $terms = get_the_terms( $product_id, 'product_cat' );
	//vd($product_id, $terms);
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return;
    }
	
    $categories    = [];
    $subcategories = [];
	
    foreach ( $terms as $term ) {
        if ( (int) $term->parent === 0 ) {
            $categories[] = $term->slug;
        } else {
            $subcategories[] = $term->slug;
        }
    }
	
    sort( $categories, SORT_STRING );
    sort( $subcategories, SORT_STRING );
	
    $category_value    = implode( ',', $categories );
    $subcategory_value = implode( ',', $subcategories );
	
	//vd($product_id, $category_value);
    // Update only if changed (important)
    if ( get_post_meta( $product_id, '_rez_sort_category', true ) !== $category_value )
	{
        update_post_meta( $product_id, '_rez_sort_category', $category_value );
    }
	
    if ( get_post_meta( $product_id, '_rez_sort_subcategory', true ) !== $subcategory_value )
	{
        update_post_meta( $product_id, '_rez_sort_subcategory', $subcategory_value );
    }
}

function rez_next_order( $column, $current_sort, $current_order ) {
    if ( $column === $current_sort ) {
        return $current_order === 'asc' ? 'desc' : 'asc';
    }
    return 'asc';
}

function rez_sort_url( $column, $current_sort, $current_order ) {
    return esc_url( add_query_arg( [
        'sort'  => $column,
        'order' => rez_next_order( $column, $current_sort, $current_order ),
        'paged' => 1, // reset pagination on sort
    ] ) );
}

if ( ! is_user_logged_in() ) {
    return;
}

$sort  = isset( $_GET['sort'] )  ? sanitize_key( $_GET['sort'] )  : 'category';
$order = isset( $_GET['order'] ) ? strtolower( $_GET['order'] ) : 'asc';

$order = $order === 'desc' ? 'DESC' : 'ASC';

$orderby  = 'date';
$meta_key = '';

switch ( $sort ) {
    case 'title':
        $orderby = 'title';
        break;

    case 'category':
        $orderby  = 'meta_value';
        $meta_key = '_rez_sort_category';
        break;

    case 'subcategory':
        $orderby  = 'meta_value';
        $meta_key = '_rez_sort_subcategory';
        break;
}

$current_sort  = isset( $_GET['sort'] )  ? sanitize_key( $_GET['sort'] )  : 'category';
$current_order = isset( $_GET['order'] ) ? strtolower( $_GET['order'] ) : 'asc';
$current_order = $current_order === 'desc' ? 'desc' : 'asc';

$current_user_id = get_current_user_id();

$paged = max(
    1,
    get_query_var( 'paged' ),
    get_query_var( 'page' )
);

$per_page = 10;

if(!empty($_GET['inactive']))
	$statuses = array( 'draft', 'private' );
else
	$statuses = array( 'publish' );

$args = array(
    'status' => $statuses,
    'author' => $current_user_id,

    'limit' => $per_page,
    'page'  => $paged,

    'orderby' => $orderby,
    'order'   => $order,

    'tax_query' => array(
        array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => array( 'product', 'services', 'packages', 'other' ),
            'operator' => 'IN',
        ),
    ),
);

if ( $meta_key ) {
    $args['meta_key'] = $meta_key;
}

$query    = new WC_Product_Query( $args );
$products = $query->get_products();

$count_args          = $args;
$count_args['limit'] = -1;
$count_args['page']  = 1;

$count_query  = new WC_Product_Query( $count_args );
$all_products = $count_query->get_products();
$total_items  = count( $all_products );
$total_pages  = (int) ceil( $total_items / $per_page );

//vd($all_products, '$all_products');

foreach ( $all_products as $product ) {
    rez_sync_product_sort_metas( $product->get_id() );
}

$top_level_categories = get_terms( [
    'taxonomy'   => 'product_cat',
    'parent'     => 0,
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
	'exclude'    => get_terms([
	    'taxonomy' => 'product_cat',
	    'slug'     => 'uncategorized',
	    'fields'   => 'ids',
	]),
] );
/*
vd($_GET, '$paged');
vd($args, '$args');
vd($products, '$products');
vd($current_user_id, '$current_user_id');
*/
?>
<script>
function close_add_product_box()
{
	var schedule_popup = document.getElementById('add_product_box');
	schedule_popup.style.display = 'none';
}

function show_add_product_box()
{
	console.log('show_add_product_box');
/*	
						show_system_message(
											'Event Saved.', 
											'response.message', 
											'success', 
											'Back To Events',
											'/events/', 
											'Create an Event',
											'/create-event/'
						);
	return false;
*/	
	//document.getElementById('add_product_box_title').innerHTML = title;
	//document.getElementById('add_product_box_text').innerHTML = text;
	
	var schedule_popup = document.getElementById('add_product_box');
	schedule_popup.style.display = 'flex';
	schedule_popup.style.zIndex = 100;
	
	return false;
}
</script>
<style>
#add_product_box input, #add_product_box select {
    height: 46px;
    padding: 0 18px;
    border-radius: 12px;
    border: 1px solid #abb5c2;
    background: #ffffff;
    font-size: 16px;
    font-weight: 500;
    color: #1f2937;
    box-shadow: inset 0 0 0 1px transparent;
}

#add_product_box label {
	text-align: left;
	width: 100%;
	display: block;
	margin: 14px 0 8px 0;
	font-weight: 600;
}

.add-product-cancel {
    border-radius: 12px;
    border: 1px solid var(--Accent-color-100, #17A0B2);
    padding: 12px 18px;
    background: transparent;
    margin-right: 0!important;
    width: 200px;
    left: 0px;
}

.add-product-add {
    color: var(--Additional-colors-White, #FFF);
    font-family: "DM Sans";
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: 140%;
    text-transform: capitalize;
    margin: 0;
    width: 200px;
    right: 0px;
    position: relative;
}
.ant-modal-confirm-btns {
	margin-top: 24px;
}

.rez-new-product-subcategory-container {
    position: relative;
}

/* Wrapper */
#rez-subcategory-suggestions {
	display: none; /* hidden by default */
    position: absolute;
    width: 100%;
    margin-top: 6px;
    background: #fff;
    border: 1px solid #e3e8ee;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    z-index: 1000;
    overflow: hidden;
}

/* show only when JS explicitly enables it */
#rez-subcategory-suggestions.is-visible {
    display: block;
}

/* Single option */
#rez-subcategory-suggestions .rez-suggestion {
    padding: 12px 16px;
    font-size: 15px;
    color: #1f2933;
    cursor: pointer;
    transition: background-color 0.15s ease;
}

/* Hover */
#rez-subcategory-suggestions .rez-suggestion:hover {
    background-color: #f3f7fa;
}

/* Optional: active / keyboard focus (future-proof) */
#rez-subcategory-suggestions .rez-suggestion.active {
    background-color: #e6f4f7;
    color: #17a0b2;
    font-weight: 500;
}
</style>
<section class="ff_add--social-media" id="add_product_box" style="display:none;">
<div class="ant-modal-mask" id="etn_multivendor_form" style="position: relative;">
	<a href="javascript:void(0)" class="close--system_message" onclick="javascript:close_add_product_box()">
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<g id="vuesax/linear/add">
				<g id="vuesax/linear/add_2">
					<g id="add">
						<path id="Vector" d="M6.46484 6.46436L13.5359 13.5354" stroke="#87888C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
						<path id="Vector_2" d="M6.46409 13.5354L13.5352 6.46436" stroke="#87888C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
					</g>
				</g>
			</g>
		</svg>
	</a>
	<div tabindex="-1" class="ant-modal-wrap ant-modal-confirm-centered etn-mltv-modal-wrapper ant-modal-centered">
		<div role="dialog" aria-modal="true" class="ant-modal css-qgg3xn ant-modal-confirm ant-modal-confirm-confirm" style="width: 416px;">
			<div tabindex="0" aria-hidden="true" style="width: 0px; height: 0px; overflow: hidden; outline: none;"></div>
			<div class="ant-modal-content">
				<div class="ant-modal-body">
					<div class="ant-modal-confirm-body-wrapper">
					    <!-- Body -->
					    <div class="rez-modal-body">
					        <!-- Category -->
					        <div class="rez-form-group">
					            <label for="rez-new-product-category">Category</label>
					            <select id="rez-new-product-category" class="rez-form-select" onchange="populate_subcategory()">
							    <?php foreach ( $top_level_categories as $cat ) : ?>
							        <option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
							    <?php endforeach; ?>
					            </select>
					        </div>
					        <!-- Subcategory -->
					        <div id="subcategory-container" class="rez-form-group rez-new-product-subcategory-container">
					            <label for="rez-new-product-subcategory ">Subcategory</label>
								<!--select id="rez-new-product-subcategory" class="rez-form-select rez-pos-subcategory">
								</select -->

								<input
								    type="text"
								    id="rez-new-product-subcategory"
								    autocomplete="off"
								    placeholder="Select or type subcategory"
								/>
								<div id="rez-subcategory-suggestions" class="rez-suggestions"></div>

					        </div>
					        <!-- Title -->
					        <div class="rez-form-group">
					            <label for="rez-new-product-title">
					                Title
					            </label>
					            <input
					                type="text"
					                id="rez-new-product-title"
					                class="rez-form-input"
					                placeholder="e.g. Black XL Sweatshirt"
					            />
					        </div>
					        <!-- Price -->
					        <div class="rez-form-group">
					            <label for="rez-new-product-price">
					                Price ($)
					            </label>
					
					            <input
					                type="number"
					                step="0.01"
					                id="rez-new-product-price"
					                class="rez-form-input"
					                placeholder="0.00"
					            />
					        </div>
					    </div>
						<div class="ant-modal-confirm-btns">
							<button onclick="javascript:close_add_product_box()" type="button" class="ant-btn css-qgg3xn ant-btn-default add-product-cancel"><span id="system_message_button1_title">Cancel</span></button>
							<button onclick="javascript:add_product()" type="button" class="ant-btn css-qgg3xn ant-btn-primary add-product-add"><span id="system_message_button2_title">Add product</span></button>
						</div>
					</div>
				</div>
			</div>
			<div tabindex="0" aria-hidden="true" style="width: 0px; height: 0px; overflow: hidden; outline: none;"></div>
		</div>
	</div>
</div>
</section>
<script>
function add_product() {

    const category    = document.getElementById('rez-new-product-category').value;
    const subcategory = document.getElementById('rez-new-product-subcategory').value.trim();
    const title       = document.getElementById('rez-new-product-title').value.trim();
    const price       = document.getElementById('rez-new-product-price').value;

    if (!title || !price || !category) {
        alert('Please fill all required fields');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'rez_add_new_product');
    formData.append('category', category);
    formData.append('subcategory', subcategory);
    formData.append('title', title);
    formData.append('price', price);

    fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert(data.data || 'Failed to add product');
            return;
        }

        // For now just reload — later we can prepend row dynamically
        window.location.reload();
    })
    .catch(() => {
        alert('Request failed');
    });
}
</script>

<?php //if ( empty( $products ) ) : ?>

    <!-- div class="rez-pos-empty">
        No products found.
    </div -->

<?php //else : ?>
<style>
.rez-pos-table input[type="text"],
.rez-pos-table input[type="number"] {
    height: 52px;
    padding: 0 18px;
    border-radius: 14px;
    border: 1px solid #dfe5eb;
    background: #ffffff;
    font-size: 16px;
    font-weight: 500;
    color: #1f2937;
    box-shadow: inset 0 0 0 1px transparent;
}

.rez-pos-table input[type="text"]:focus,
.rez-pos-table input[type="number"]:focus {
    outline: none;
    border-color: #0ea5b7;
    box-shadow: 0 0 0 2px rgba(14,165,183,0.12);
}
.rez-pos-price {
    text-align: left;
    font-weight: 600;
}
.rez-pos-table select {
    height: 52px;
    padding: 0 44px 0 18px;
    border-radius: 14px;
    border: 1px solid #dfe5eb;
    background-color: #ffffff;
    font-size: 16px;
    font-weight: 500;
    color: #1f2937;
    cursor: pointer;
}

.rez-pos-table select:focus {
    outline: none;
    border-color: #0ea5b7;
    box-shadow: 0 0 0 2px rgba(14,165,183,0.12);
}
.rez-pos-disable {
    height: 48px;
    padding: 0 22px;
    border-radius: 14px;
    background: #ffffff;
    border: 2px solid #ef4d2f;
    color: #ef4d2f;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.rez-pos-disable:hover {
    background: #ef4d2f;
    color: #ffffff;
}
.rez-pos-activate {
    height: 48px;
    padding: 0 22px;
    border-radius: 14px;
    background: #ffffff;
    border: 2px solid #0ea5b7;
    color: #0ea5b7;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.rez-pos-activate:hover {
    background: #0ea5b7;
    color: #ffffff;
}
.rez-pos-row.is-disabled {
    /*opacity: 0.6;*/
}

.rez-pos-row.is-disabled input,
.rez-pos-row.is-disabled select {
    /*pointer-events: none;*/
}
.rez-pos-table td {
    padding: 18px 16px;
}
.rez-multiselect {
    position: relative;
    width: 100%;
}

.rez-multiselect-trigger {
    width: 100%;
    height: 52px;
    padding: 0 18px;
    border-radius: 14px;
    border: 2px solid #b6c0cb;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 18px;
    cursor: pointer;
}

.rez-multiselect-arrow {
    width: 10px;
    height: 10px;
    border-right: 3px solid #111;
    border-bottom: 3px solid #111;
    transform: rotate(45deg);
}

.rez-multiselect-dropdown {
	width: fit-content;
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    padding: 12px 0;
    display: none;
    z-index: 100;
}

.rez-multiselect-option {
    padding: 14px 22px;
    display: flex;
    align-items: center;
    font-size: 20px;
    cursor: pointer;
}

.rez-multiselect-option:hover {
    background: #f4f7fa;
}

.rez-multiselect-option .check {
    width: 22px;
    height: 22px;
    margin-right: 16px;
    position: relative;
}

.rez-multiselect-option.selected .check::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 0;
    width: 8px;
    height: 14px;
    border-right: 4px solid #0ea5b7;
    border-bottom: 4px solid #0ea5b7;
    transform: rotate(45deg);
}

.rez-pos-table input[type="number"],
.rez-pos-table input[type="number"]::-webkit-inner-spin-button,
.rez-pos-table input[type="number"]::-webkit-outer-spin-button {
    color: #17a0b2;
}

.rez-pos-price-cell {
    position: relative;
}

.rez-pos-price-cell::before {
    content: '$';
    position: absolute;
    left: 30px;
    top: 51%;
    transform: translateY(-50%);
    color: #17a0b2;
    font-weight: 600;
    pointer-events: none;
}

.rez-pos-price-cell input[type="number"], .rez-pos-price-cell input.rez-pos-price {
    padding-left: 28px; /* space for $ */
    color: #17a0b2;
}
.rez-sort-icon {
    display: inline-block;
    margin-left: 6px;
    width: 8px;
    height: 8px;
    border-right: 2px solid #6b7280;
    border-bottom: 2px solid #6b7280;
}

.rez-sort-asc {
    transform: rotate(-135deg);
}

.rez-sort-desc {
    transform: rotate(45deg);
}

.rez-pos-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin: 24px 0;
}

.rez-pos-pagination .rez-page {
    min-width: 36px;
    height: 36px;
    padding: 0 10px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    font-size: 14px;
    font-weight: 500;
    text-decoration: none;

    color: #17a0b2;
    border: 1.5px solid #17a0b2;
    border-radius: 8px;
    background: #fff;

    transition: all 0.15s ease;
}

.rez-pos-pagination .rez-page:hover {
    background: rgba(23, 160, 178, 0.08);
}

.rez-pos-pagination .rez-page.active,
.rez-pos-pagination .rez-page.current {
    background: #0f8f9c;
    border-color: #0f8f9c;
    color: #fff;
    pointer-events: none;
}

.rez-pos-pagination .rez-page.prev,
.rez-pos-pagination .rez-page.next {
    font-size: 18px;
    padding: 0;
}

.rez-pos-pagination .rez-page.disabled {
    opacity: 0.4;
    pointer-events: none;
}
.rez-sortable a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: inherit;
    text-decoration: none;
}

.rez-sort-arrows {
    display: inline-flex;
    flex-direction: column;
    gap: 2px;
}

.rez-sort-arrow {
    width: 6px;
    height: 6px;
    border-right: 2px solid #b0b8bf; /* gray */
    border-bottom: 2px solid #b0b8bf;
    opacity: 0.8;
}

.rez-sort-arrow.up {
    transform: rotate(-135deg);
}

.rez-sort-arrow.down {
    transform: rotate(45deg);
}

.rez-sort-arrow.active {
    border-color: #000; /* black */
    opacity: 1;
}
.events_page_actions {
    position: relative;
    top: initial;
	margin-bottom: 32px;
}
</style>
<script>
function switch_day(checkbox_id)
{
	var span_closed = document.getElementById('closed_' + checkbox_id);
	var span_open = document.getElementById('open_' + checkbox_id);
	var posts_container = document.getElementById('posts-container');
	
	console.log('span_closed.style.display');
	console.log(span_closed.style.display);
	
	if(span_closed.style.display == 'none')
	{
		span_closed.style.display = 'block';
		span_open.style.display = 'none';
	}
	else
	{
		span_closed.style.display = 'none';
		span_open.style.display = 'block';
	}
	
	if(document.getElementById('checkbox_' + checkbox_id).checked)
	{
		window.location.href = '/products/?inactive=1';
		//file = 'event-posts.php';
		//setCookie("page_view", "grid", 7);
		//posts_container.classList.remove('events_list');
	}
	else
	{
		window.location.href = '/products/';
		//file = 'event-posts-list.php';
		//setCookie("page_view", "list", 7);
		//posts_container.classList.add('events_list');
	}
	
	//paged = 0;
	//document.getElementById('posts-container').innerHTML = '';
	//get_events_ajax();
}

function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); // Convert days to milliseconds
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}
</script>
<style>
.events-page-wrapper .etn_search_shortcode.etn_search_wrapper {
    margin-top: 12px;
}

.events-page-wrapper .etn_search_shortcode.etn_search_wrapper h3 {
    padding-top: initial;
}

.status--btn {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    min-width: 165px;
	margin-top: 12px;
}
.status--btn .switch {
    position: relative;
    display: inline-block;
    width: 36px;
    height: 20px
}
.status--btn .switch input {
    opacity: 0;
    width: 0;
    height: 0;
    outline: none
}
.status--btn .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #eaeaea;
    -webkit-transition: .3s;
    transition: .3s
}
.status--btn .slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background-color: #fff;
    -webkit-transition: .3s;
    transition: .3s
}
.status--btn input:checked+.slider {
    background-color: #117886;
}
.status--btn input:focus+.slider {
    box-shadow: 0 0 1px #117886;
}
.status--btn input:checked+.slider:before {
    -webkit-transform: translateX(16px);
    -ms-transform: translateX(16px);
    transform: translateX(16px)
}
.status--btn .slider.round {
    border-radius: 20px
}
.status--btn .slider.round:before {
    border-radius: 50%
}
.status--btn .status--text {
    font-size: 16px;
    line-height: 1;
    font-weight: 400;
    margin-left: 15px
}
.status--btn {
    justify-content: initial;
}

.switch_lanel {
    font-size: 16px;
}
</style>
<div class="rez-pos-table-wrapper attendees-box">
	<div class="dashboard-widget events events-page-wrapper">
		<a href="/products/" style="color:#17a0b2;">Product List</a>&nbsp;
		<a href="/product-checkout/" class="past_events_a" style="color:#17a0b2;">Product Checkout</a>&nbsp;
		<a href="/products-report/" class="past_events_a" style="color:#17a0b2;">Reports For POS</a>&nbsp;
		<div class="events_page_actions">
		<a href="javascript:void(0)" onclick="javascript:show_add_product_box()" class="etn-btn etn-btn-primary add_event">Add Product</a>
		</div>
	</div>
	<div>
		<div class="status--btn">
				<span class="switch_lanel">Active&nbsp;&nbsp;</span><label class="switch">
				<input name="checkbox_inactive_products" id="checkbox_inactive_products" type="checkbox"<?php if(empty($_GET['inactive'])) echo ' checked';?>>
				<span onclick="switch_day('inactive_products')" class="slider round"></span>
				<span class="status--text"></span>
			</label>
			<span id="closed_inactive_products" class="status--text closed" style="display: block;"></span>
			<span id="open_inactive_products" class="status--text open" style="display: none;"></span>
		</div>				
	</div>				
    <table class="rez-pos-table events-table attendees-table">
		<thead>
		<tr>
			<th class="rez-sortable">
			    <a href="<?php echo rez_sort_url( 'category', $current_sort, $current_order ); ?>">
			        Category
			        <span class="rez-sort-arrows">
			            <span class="rez-sort-arrow up <?php
			                echo ( $current_sort === 'category' && $current_order === 'asc' ) ? 'active' : '';
			            ?>"></span>
			            <span class="rez-sort-arrow down <?php
			                echo ( $current_sort === 'category' && $current_order === 'desc' ) ? 'active' : '';
			            ?>"></span>
			        </span>
			    </a>
			</th>
			<th class="rez-sortable">
			    <a href="<?php echo rez_sort_url( 'subcategory', $current_sort, $current_order ); ?>">
			        Subcategory
			        <span class="rez-sort-arrows">
			            <span class="rez-sort-arrow up <?php
			                echo ( $current_sort === 'subcategory' && $current_order === 'asc' ) ? 'active' : '';
			            ?>"></span>
			            <span class="rez-sort-arrow down <?php
			                echo ( $current_sort === 'subcategory' && $current_order === 'desc' ) ? 'active' : '';
			            ?>"></span>
			        </span>
			    </a>
			</th>
			<th class="rez-sortable">
			    <a href="<?php echo rez_sort_url( 'title', $current_sort, $current_order ); ?>">
			        Title
			        <span class="rez-sort-arrows">
			            <span class="rez-sort-arrow up <?php
			                echo ( $current_sort === 'title' && $current_order === 'asc' ) ? 'active' : '';
			            ?>"></span>
			            <span class="rez-sort-arrow down <?php
			                echo ( $current_sort === 'title' && $current_order === 'desc' ) ? 'active' : '';
			            ?>"></span>
			        </span>
			    </a>
			</th>
		    <th>Price</th>
		    <th>Actions</th>
		</tr>
		</thead>

        <tbody>
        <?php foreach ( $products as $product ) : ?>

            <?php
            $product_id = $product->get_id();
            $price      = $product->get_price();

            /**
             * Second-level category (subcategory)
             */
            $subcategory = '';
            $terms = get_the_terms( $product_id, 'product_cat' );

            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) {
                    if ( $term->parent !== 0 ) {
                        $subcategory = $term->name;
                        break;
                    }
                }
            }

            $is_disabled = $product->get_status() !== 'publish';
            ?>

            <tr
                class="rez-pos-row <?php echo $is_disabled ? 'is-disabled' : ''; ?>"
                data-product-id="<?php echo esc_attr( $product_id ); ?>"
            >

                <!-- Category -->
                <td>
                    <?php 
					//$top_category = rez_get_top_level_product_category_slug( $product_id ); 
					
					
$assigned_terms = get_the_terms( $product_id, 'product_cat' );
$selected_slugs = [];

if ( ! empty( $assigned_terms ) && ! is_wp_error( $assigned_terms ) ) {
    foreach ( $assigned_terms as $term ) {
        if ( $term->parent === 0 ) { // top-level only
            $selected_slugs[] = $term->slug;
        }
    }
}

					
					
					?>

<select class="rez-pos-category" multiple>

    <?php foreach ( $top_level_categories as $cat ) : ?>

        <option
            value="<?php echo esc_attr( $cat->slug ); ?>"
            <?php echo in_array( $cat->slug, $selected_slugs, true ) ? 'selected' : ''; ?>
        >
            <?php echo esc_html( $cat->name ); ?>
        </option>

    <?php endforeach; ?>

</select>

                </td>

                <!-- Subcategory -->
                <td>
<?php
$assigned_terms = get_the_terms( $product_id, 'product_cat' );

$selected_category_slugs   = [];
$selected_subcategory_slugs = [];

if ( ! empty( $assigned_terms ) && ! is_wp_error( $assigned_terms ) ) {
    foreach ( $assigned_terms as $term ) {
        if ( $term->parent === 0 ) {
            $selected_category_slugs[] = $term->slug;
        } else {
            $selected_subcategory_slugs[] = $term->slug;
        }
    }
}

$subcategories = rez_get_product_subcategories_by_parents(
    $selected_category_slugs
);
?>
<select class="rez-pos-subcategory" multiple>

    <?php foreach ( $subcategories as $subcat ) : ?>

        <option
            value="<?php echo esc_attr( $subcat->slug ); ?>"
            <?php echo in_array( $subcat->slug, $selected_subcategory_slugs, true ) ? 'selected' : ''; ?>
        >
            <?php echo esc_html( $subcat->name ); ?>
        </option>

    <?php endforeach; ?>

</select>
                </td>

                <!-- Title -->
                <td>
                    <input
                        type="text"
                        class="rez-pos-title"
                        value="<?php echo esc_attr( $product->get_name() ); ?>"
                    />
                </td>

                <!-- Price -->
                <td class="rez-pos-price-cell">
                    <input
                        type="number"
                        step="0.01"
                        class="rez-pos-price"
                        value="<?php echo esc_attr( $price ); ?>"
                    />
                </td>

                <!-- Actions -->
                <td>
                    <?php if ( $is_disabled ) : ?>
                        <button class="rez-pos-activate">Activate</button>
                    <?php else : ?>
                        <button class="rez-pos-disable">Disable</button>
                    <?php endif; ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>

<?php if ( $total_pages > 1 ) : ?>
    <div class="rez-pos-pagination">

        <!-- Previous -->
        <?php if ( $paged > 1 ) : ?>
            <a
                class="rez-page prev"
                href="<?php echo esc_url( get_pagenum_link( $paged - 1 ) ); ?>"
                aria-label="Previous page"
            >
                <
            </a>
        <?php else : ?>
            <span class="rez-page prev disabled"><</span>
        <?php endif; ?>

        <!-- Page numbers -->
        <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
            <?php if ( $i === $paged ) : ?>
                <span class="rez-page current">
                    <?php echo $i; ?>
                </span>
            <?php else : ?>
                <a
                    class="rez-page"
                    href="<?php echo esc_url( get_pagenum_link( $i ) ); ?>"
                >
                    <?php echo $i; ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>

        <!-- Next -->
        <?php if ( $paged < $total_pages ) : ?>
            <a
                class="rez-page next"
                href="<?php echo esc_url( get_pagenum_link( $paged + 1 ) ); ?>"
                aria-label="Next page"
            >
                >
            </a>
        <?php else : ?>
            <span class="rez-page next disabled">></span></span>
        <?php endif; ?>

    </div>
<?php endif; ?>

</div>

<?php //endif; ?>
<script>
/*
document.addEventListener('click', function (e) {

    const option = e.target.closest('.rez-multiselect-option');
    if (!option) {
        return;
    }

    const multiselect = option.closest('.rez-multiselect');
    const row = option.closest('tr');
    if (!multiselect || !row) {
        return;
    }

    const productId = row.dataset.productId;
    if (!productId) {
        return;
    }

    const select = row.querySelector('select');
    if (!select) {
        return;
    }

     //CATEGORY multiselect
    if (select.classList.contains('rez-pos-category')) {

        // single-select behavior enforced here
        multiselect
            .querySelectorAll('.rez-multiselect-option')
            .forEach(o => {
                if (o !== option) o.classList.remove('selected');
            });

        option.classList.add('selected');
        select.value = option.dataset.value;

        const formData = new FormData();
        formData.append('action', 'rez_save_product_category');
        formData.append('product_id', productId);
        formData.append('category', option.dataset.value);

		console.log('category');
		console.log(option.dataset.value);

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert('Failed to save category');
            }
        });

        return;
    }

    //SUBCATEGORY multiselect
    if (select.classList.contains('rez-pos-subcategory')) {

        // single-select behavior
        multiselect
            .querySelectorAll('.rez-multiselect-option')
            .forEach(o => {
                if (o !== option) o.classList.remove('selected');
            });

        option.classList.add('selected');
        select.value = option.dataset.value;

        const categorySelect = row.querySelector('.rez-pos-category');
        const category = categorySelect ? categorySelect.value : '';

        if (!category) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'rez_save_product_subcategory');
        formData.append('product_id', productId);
        formData.append('category', category);
        formData.append('subcategory', option.dataset.value);
		
		console.log('category');
		console.log(category);
		console.log('subcategory');
		console.log(option.dataset.value);

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert('Failed to save subcategory');
            }
        });

        return;
    }
});
*/
</script>
<script>
(function () {

    function saveTitle(input) {
        const row = input.closest('tr');
        const productId = row.dataset.productId;
        const title = input.value.trim();

        if (!title) {
            return;
        }

        // prevent duplicate saves
        if (input.dataset.lastSaved === title) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'rez_save_product_title');
        formData.append('product_id', productId);
        formData.append('title', title);

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.dataset.lastSaved = title;
            } else {
                alert('Failed to save title');
            }
        });
    }

    // Save on Enter
    document.addEventListener('keydown', function (e) {

        if (!e.target.classList.contains('rez-pos-title')) {
            return;
        }

        if (e.key !== 'Enter') {
            return;
        }

        e.preventDefault();
        saveTitle(e.target);
        e.target.blur();
    });

    // Save on blur
    document.addEventListener('blur', function (e) {

        if (!e.target.classList.contains('rez-pos-title')) {
            return;
        }

        saveTitle(e.target);

    }, true); // ?? capture phase is important for blur

})();
</script>
<script>
(function () {

    function savePrice(input) {
        const row = input.closest('tr');
        const productId = row.dataset.productId;
        const price = input.value.trim();

        if (price === '') {
            return;
        }

        // Prevent duplicate saves
        if (input.dataset.lastSaved === price) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'rez_save_product_price');
        formData.append('product_id', productId);
        formData.append('price', price);

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.dataset.lastSaved = price;
            } else {
                alert('Failed to save price');
            }
        });
    }

    // Save on Enter
    document.addEventListener('keydown', function (e) {

        if (!e.target.classList.contains('rez-pos-price')) {
            return;
        }

        if (e.key !== 'Enter') {
            return;
        }

        e.preventDefault();
        savePrice(e.target);
        e.target.blur();
    });

    // Save on blur
    document.addEventListener('blur', function (e) {

        if (!e.target.classList.contains('rez-pos-price')) {
            return;
        }

        savePrice(e.target);

    }, true); // capture phase required

})();
</script>
<script>
document.addEventListener('click', function (e) {

    if (
        !e.target.classList.contains('rez-pos-disable') &&
        !e.target.classList.contains('rez-pos-activate')
    ) {
        return;
    }

    e.preventDefault();

    const button = e.target;
    const row = button.closest('tr');
    const productId = row.dataset.productId;

    const formData = new FormData();
    formData.append('action', 'rez_toggle_product_status');
    formData.append('product_id', productId);

    fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(r => r.json())
    .then(data => {

        if (!data.success) {
            alert('Failed to update product status');
            return;
        }

        const isDisabled = data.data.new_status !== 'publish';

        // Update row state
        row.classList.toggle('is-disabled', isDisabled);

        // Swap button
        if (isDisabled) {
            button.textContent = 'Activate';
            button.classList.remove('rez-pos-disable');
            button.classList.add('rez-pos-activate');
        } else {
            button.textContent = 'Disable';
            button.classList.remove('rez-pos-activate');
            button.classList.add('rez-pos-disable');
        }
    });
});
</script>
<script>
//(function () {

    let openMultiselect = null;

    /* =========================
       MULTISELECT BUILD
    ========================= */

    function buildMultiselect(select) {
        if (select.dataset.rezMultiselectBuilt) return;
		
		if (select.classList.contains('rez-form-select')) return;

        select.style.display = 'none';

        const wrapper = document.createElement('div');
        wrapper.className = 'rez-multiselect';

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'rez-multiselect-trigger';

        const label = document.createElement('span');
        label.className = 'rez-multiselect-label';

        const arrow = document.createElement('span');
        arrow.className = 'rez-multiselect-arrow';

        trigger.append(label, arrow);

        const dropdown = document.createElement('div');
        dropdown.className = 'rez-multiselect-dropdown';

        rebuildDropdownFromSelect(select, dropdown);
        updateLabel(label, dropdown);

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();

            if (openMultiselect && openMultiselect !== wrapper) {
                closeMultiselect(openMultiselect);
            }

            dropdown.style.display = 'block';
            openMultiselect = wrapper;
        });

        wrapper.append(trigger, dropdown);
        select.after(wrapper);

        select.dataset.rezMultiselectBuilt = '1';
    }

function rebuildDropdownFromSelect(select, dropdown) {
    dropdown.innerHTML = '';

    Array.from(select.options).forEach(opt => {
        if (!opt.value) return;

        const option = document.createElement('div');
        option.className = 'rez-multiselect-option';
        option.dataset.value = opt.value;
        option.innerHTML = `<span class="check"></span>${opt.text}`;

        if (opt.selected) {
            option.classList.add('selected');
        }

        option.addEventListener('click', function (e) {
            e.stopPropagation();

            // ?? SINGLE SELECT LOGIC
            dropdown.querySelectorAll('.rez-multiselect-option')
                .forEach(o => o.classList.remove('selected'));

            option.classList.add('selected');

            // sync <select>
            Array.from(select.options).forEach(o => {
                o.selected = (o.value === option.dataset.value);
            });

            updateLabel(dropdown.previousSibling, dropdown);

            // close + save
            closeMultiselect(dropdown.closest('.rez-multiselect'));
            openMultiselect = null;
        });

        dropdown.appendChild(option);
    });
}
/*
    function syncSelect(select, dropdown) {
        const selectedValues = Array.from(
            dropdown.querySelectorAll('.rez-multiselect-option.selected')
        ).map(o => o.dataset.value);

        Array.from(select.options).forEach(opt => {
            opt.selected = selectedValues.includes(opt.value);
        });
    }
*/
function updateLabel(label, dropdown) {
    const selected = dropdown.querySelector('.rez-multiselect-option.selected');
    label.textContent = selected ? selected.textContent.trim() : 'Select';
}

    /* =========================
       CLOSE + SAVE
    ========================= */

    function closeMultiselect(wrapper) {
        wrapper.querySelector('.rez-multiselect-dropdown').style.display = 'none';
        triggerSave(wrapper);
    }

    function triggerSave(wrapper) {
        const row = wrapper.closest('tr');
        if (!row) return;

        const select = wrapper.previousElementSibling;
        const productId = row.dataset.productId;
        if (!select || !productId) return;

        const values = Array.from(select.selectedOptions).map(o => o.value);

        if (select.classList.contains('rez-pos-category')) {
            saveCategory(productId, values);
            updateSubcategories(row, values);
        }

        if (select.classList.contains('rez-pos-subcategory')) {
            const catSelect = row.querySelector('.rez-pos-category');
            const cats = catSelect
                ? Array.from(catSelect.selectedOptions).map(o => o.value)
                : [];

            saveSubcategory(productId, cats, values);
        }
    }

    /* =========================
       SUBCATEGORY UPDATE (NEW)
    ========================= */

    function updateSubcategories(row, categories) {
		console.log('updateSubcategories categories');
		console.log(categories);
	
        const subSelect = row.querySelector('select.rez-pos-subcategory');
        if (!subSelect) return;

        const existingSelected = Array.from(subSelect.selectedOptions).map(o => o.value);

        const formData = new FormData();
        formData.append('action', 'rez_get_subcategories_by_parents');
        formData.append('parents', categories.join(','));

        fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
			console.log('data');
			console.log(data);
            if (!data.success) return;

            subSelect.innerHTML = '';

            data.data.forEach(term => {
                const opt = document.createElement('option');
                opt.value = term.slug;
                opt.textContent = term.name;
                opt.selected = existingSelected.includes(term.slug);
                subSelect.appendChild(opt);
            });

            // rebuild multiselect UI
            const wrapper = subSelect.nextElementSibling;
            if (wrapper && wrapper.classList.contains('rez-multiselect')) {
                wrapper.remove();
                delete subSelect.dataset.rezMultiselectBuilt;
                buildMultiselect(subSelect);
            }
        });
    }

    /* =========================
       AJAX SAVE
    ========================= */

    function saveCategory(productId, values) {
        const fd = new FormData();
        fd.append('action', 'rez_save_product_category');
        fd.append('product_id', productId);
        fd.append('category', values.join(','));

        fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        });
    }

    function saveSubcategory(productId, categories, subcategories) {
	
		console.log('categories');
		console.log(categories);
		console.log('subcategories');
		console.log(subcategories);
	
        const fd = new FormData();
        fd.append('action', 'rez_save_product_subcategory');
        fd.append('product_id', productId);
        fd.append('category', categories.join(','));
        fd.append('subcategory', subcategories.join(','));

        fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        });
    }

    /* =========================
       GLOBAL EVENTS
    ========================= */

    document.addEventListener('click', function () {
        if (openMultiselect) {
            closeMultiselect(openMultiselect);
            openMultiselect = null;
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document
            .querySelectorAll('select.rez-pos-category, select.rez-pos-subcategory')
            .forEach(buildMultiselect);
    });

//})();
</script>
<script>
function populate_subcategory()
{
	//updateSubcategories(row, categories)
	var row = document.getElementById('subcategory-container');
	var category = document.getElementById('rez-new-product-category').value;
	var categories = [category];
	
	console.log('categories');
	console.log(categories);
	
	updateSubcategories(row, categories);
}
</script>
<script>
(function () {

    const input = document.getElementById('rez-new-product-subcategory');
    const list  = document.getElementById('rez-subcategory-suggestions');
    const categorySelect = document.getElementById('rez-new-product-category');

    let lastQuery = null;
    let selectedSlug = null;

    if (!input || !list || !categorySelect) return;

    /* -------------------------
     * Helpers
     * ------------------------- */

    function hideList() {
        list.innerHTML = '';
        list.classList.remove('is-visible');
    }

    function showList() {
        list.classList.add('is-visible');
    }

    function fetchSuggestions(query) {
        const category = categorySelect.value;

        const formData = new FormData();
        formData.append('action', 'rez_search_product_subcategories');
        formData.append('category', category);
        formData.append('q', query);

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            list.innerHTML = '';

            if (!res.success || !res.data || !res.data.length) {
                hideList();
                return;
            }

            res.data.forEach(item => {
                const div = document.createElement('div');
                div.className = 'rez-suggestion';
                div.textContent = item.name;
                div.dataset.slug = item.slug;

                div.addEventListener('click', () => {
                    input.value = item.name;
                    selectedSlug = item.slug;
                    hideList();
                });

                list.appendChild(div);
            });

            showList();
        });
    }

    /* -------------------------
     * INPUT — typing
     * ------------------------- */
    input.addEventListener('input', function () {
        const query = input.value.trim();

        selectedSlug = null;

        if (query === lastQuery) return;
        lastQuery = query;

        fetchSuggestions(query);
    });

    /* -------------------------
     * INPUT — focus (show all)
     * ------------------------- */
    input.addEventListener('focus', function () {
        const query = input.value.trim();

        lastQuery = null; // force fetch
        fetchSuggestions(query);
    });

    /* -------------------------
     * Outside click ? close
     * ------------------------- */
    document.addEventListener('click', function (e) {
        if (!list.contains(e.target) && e.target !== input) {
            hideList();
        }
    });

    /* -------------------------
     * Expose selected value
     * ------------------------- */
    window.rezGetSelectedSubcategory = function () {
        return {
            name: input.value.trim(),
            slug: selectedSlug
        };
    };

})();
</script>
<br>
<br>
<br>
<br>
<br>
<br>

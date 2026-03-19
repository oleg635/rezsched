<?php
$title = $content = '';
$contact_name = $contact_phone = $full_name = $date_signed = '';
$current_user = wp_get_current_user();
//$user_id = 0;
$user_id = $current_user_id = get_current_user_id();
$user_roles = $current_user->roles;
$user_role = 'subscriber';
if($user_roles)
	$user_role = $user_roles[0];

$post_id = 0;

if($waiver_name)
{
	$args = array(
	    'name'        => $waiver_name,
	    'post_type'   => 'waiver',
	    'post_status' => 'publish',
	    'numberposts' => 1
	);
	
	$posts = get_posts($args);
	
	if($posts)
	{
		//$user_id = $posts[0]->post_author;
	    $post_id = $posts[0]->ID;
		$post = get_post($post_id);
		$content = $post->post_content;
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
		$title = $post->post_title;
	}
	else
	{
	    echo 'No post found with the slug: ' . $waiver_name;
	}
}

$confirm_checked = '';

$meta_key = 'waiver_' . $post_id;
$sign = get_user_meta($current_user_id, $meta_key, TRUE);
if($sign)
	$confirm_checked = ' checked';


//vd($user_role, '$user_role');

if($user_id)
{
	if($user_role !== 'seller')
	{
		$user_info = get_userdata($user_id);
		$email = $user_info->user_email;
		
		$date_signed = '';//$user_info->user_registered;
		$meta_key = 'waiver_' . $post_id;
		$sign = get_user_meta($user_id, $meta_key, TRUE);
		//var_dump($user_id, '$user_id');
		//var_dump($sign, '$sign');
		if($sign)
		{
			$confirm_checked = ' checked';
			$date_signed = date('Y-m-d', $sign);
		}
		
		$first_name = get_user_meta($user_id, 'first_name', TRUE);
		$last_name = get_user_meta($user_id, 'last_name', TRUE);
		$full_name = $first_name . ' ' . $last_name;
		
		$contact_first_name = get_user_meta($user_id, 'contact_first_name', TRUE);
		$contact_last_name = get_user_meta($user_id, 'contact_last_name', TRUE);
		$contact_name = $contact_first_name . ' ' . $contact_last_name;
		
		$contact_phone = get_user_meta($user_id, 'contact_phone', TRUE);
	}
}


?>
<style>
.ff_add--social-media {
    background: rgba(0, 0, 0, .1);
    padding: 10%;
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100vh;
    overflow: hidden;
}

.ff_add--social-media .social_media--popup {
	border-radius: 20px;
	background: #FFF;
	padding: 50px;
	width: 100%;
	max-width: 870px;
	height: auto;
	margin: 0 auto;
	position: relative;
	overflow: hidden;
}

.social_media--popup .popupt--title {
	color: /*var(--e-global-title-color);*/
	font-size: 24px;
	line-height: 1.4;
	font-weight: 700;
	margin: 0 0 12px 0;
}
.add--link-fields label {
	display: block;
	/*color: var(--e-global-text-color);*/
	font-size: 12px;
	font-weight: 600;
	line-height: 1.6;
	margin-bottom: 4px;
}

.add--link-fields input {
	height: 52px;
	padding: 6px 15px;
	border-radius: 20px;
	border: 1px solid var(--line, #EAEAEA);
	/*color: var(--e-global-title-color);*/
	font-size: 14px;
	font-weight: 400;
	line-height: 1.4;
	outline: none;
	display: block;
	width: 100%;
}

input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active{
	-webkit-background-clip: text;
  transition: background-color 5000s ease-in-out 0s;
  box-shadow: inset 0 0 20px 20px #FFF;
}

.add--link-fields a.add-btn {
	display: inline-block;
	font-family: inherit;
	margin-top: 30px;
	border-radius: 20px;
	background: var(--Accent-color-100, #17A0B2);
	padding: 16px 35px;
	text-align: center;
	text-decoration: none;
	color: #FFF;
	font-size: 18px;
	font-weight: 600;
	line-height: 23px;
	float: right;
}

.ff_add--social-media .close--popup {
	display: inline-block;
	position: absolute;
	right: 50px;
	top: 56px;
	width: 20px;
	height: 20px;
	cursor: pointer;
}

.ff_add--social-media .message_box_text {
	font-size: 18px;
}
</style>
<script>
function close_message_box()
{
	var schedule_popup = document.getElementById('message_box');
	schedule_popup.style.display = 'none';
}

function show_message_box(title, text)
{
	console.log('show_message_box');
	document.getElementById('message_box_title').innerHTML = title;
	document.getElementById('message_box_text').innerHTML = text;
	
	var schedule_popup = document.getElementById('message_box');
	schedule_popup.style.display = 'flex';
	schedule_popup.style.zIndex = 100;
	
	return false;
}

function close_system_message()
{
	var schedule_popup = document.getElementById('system_message');
	schedule_popup.style.display = 'none';
}

function show_system_message(title, text, class_name)
{
	console.log('show_system_message');
	document.getElementById('system_message_title').innerHTML = title;
	
	var txt = text.replaceAll('[', '<');
	txt = txt.replaceAll(']', '>');
	
	document.getElementById('system_message_text').innerHTML = txt;
	
	var social_media = document.getElementById('system_message');
	social_media.className = 'ff_add--social-media';
	social_media.classList.add('system_message_' + class_name);
	
	var schedule_popup = document.getElementById('system_message');
	schedule_popup.style.display = 'flex';
	schedule_popup.style.zIndex = 100;
	
	return false;
}
</script>		

<section class="ff_add--social-media" id="message_box" style="display:none;">
<div class="social_media--popup">
<a href="javascript:void(0)" class="close--popup" onclick="javascript:close_message_box()">
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
<h2 class="popupt--title" id="message_box_title"></h2>
<div style id="message_box_text" class="message_box_text"></div>
<div class="add--link-fields">
<a onclick="javascript:close_message_box()" href="javascript:void(0)" class="add-btn">Close</a>
</div>
</div>
</section>

<section class="ff_add--social-media" id="system_message" style="display:none;">
<div class="ant-modal-mask" id="etn_multivendor_form">
	<div tabindex="-1" class="ant-modal-wrap ant-modal-confirm-centered etn-mltv-modal-wrapper ant-modal-centered">
		<div role="dialog" aria-modal="true" class="ant-modal css-qgg3xn ant-modal-confirm ant-modal-confirm-confirm" style="width: 416px;">
			<div tabindex="0" aria-hidden="true" style="width: 0px; height: 0px; overflow: hidden; outline: none;"></div>
			<div class="ant-modal-content">
				<div class="ant-modal-body">
					<div class="ant-modal-confirm-body-wrapper">
						<div class="ant-modal-confirm-body ant-modal-confirm-body-has-title"><span role="img" aria-label="check-circle" class="anticon anticon-check-circle" style="font-size: 50px;"><svg viewBox="64 64 896 896" focusable="false" data-icon="check-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm0 820c-205.4 0-372-166.6-372-372s166.6-372 372-372 372 166.6 372 372-166.6 372-372 372z" fill="#5d5dff"></path><path d="M512 140c-205.4 0-372 166.6-372 372s166.6 372 372 372 372-166.6 372-372-166.6-372-372-372zm193.4 225.7l-210.6 292a31.8 31.8 0 01-51.7 0L318.5 484.9c-3.8-5.3 0-12.7 6.5-12.7h46.9c10.3 0 19.9 5 25.9 13.3l71.2 98.8 157.2-218c6-8.4 15.7-13.3 25.9-13.3H699c6.5 0 10.3 7.4 6.4 12.7z" fill="#f0f2ff"></path><path d="M699 353h-46.9c-10.2 0-19.9 4.9-25.9 13.3L469 584.3l-71.2-98.8c-6-8.3-15.6-13.3-25.9-13.3H325c-6.5 0-10.3 7.4-6.5 12.7l124.6 172.8a31.8 31.8 0 0051.7 0l210.6-292c3.9-5.3.1-12.7-6.4-12.7z" fill="#5d5dff"></path></svg></span>
							<div class="ant-modal-confirm-paragraph"><span id="system_message_title" class="ant-modal-confirm-title">Account Saved.</span>
								<div id="system_message_text" class="ant-modal-confirm-content">Your Account has been saved. Take next action from the below button.</div>
							</div>
						</div>
						<div class="ant-modal-confirm-btns">
							<button type="button" class="ant-btn css-qgg3xn ant-btn-default" onclick="javascript:close_system_message()"><span>Close</span></button>
							<!-- button type="button" class="ant-btn css-qgg3xn ant-btn-primary" onclick="javascript:close_system_message()"><span>Create another</span></button -->
						</div>
					</div>
				</div>
			</div>
			<div tabindex="0" aria-hidden="true" style="width: 0px; height: 0px; overflow: hidden; outline: none;"></div>
		</div>
	</div>
</div>
</section>
<style>
.arrow-down {
	background-image: url('/wp-content/themes/greenshift-child/img/arrow-down.png');
	width:12px;
	height:7px;
	display: inline-block;
	/*float: right;*/
	margin: 11px;
}
.arrow-up {
	background-image: url('/wp-content/themes/greenshift-child/img/arrow-up.png');
	width:12px;
	height:7px;
	display: inline-block;
	/*float: right;*/
	margin: 11px;
}
.order_child {
	display: none;
}
.edit-post-actions h4 {
	display: inline-block;
}
</style>
<script>
function expand_block(block_id)
{
	var subblocks = jQuery('.' + block_id + '_subblock');

	//var block = document.getElementById('block_id');
	var arrow = document.getElementById('arrow_' + block_id);
	
	if(arrow.classList.contains('arrow-down'))
	{
		subblocks.each(function() {
		        jQuery(this).slideDown('slow');
		});
			
		arrow.classList.remove('arrow-down');
		arrow.classList.add('arrow-up');
	}
	else
	{
		subblocks.each(function() {
		        jQuery(this).slideUp('slow');
		});
			
		arrow.classList.remove('arrow-up');
		arrow.classList.add('arrow-down');
	}
}
</script>
<div class="waiver-container">
	<div class="waiver-column-left">
		<h3><?=$title?></h3>
		<div class="waiver-content">
			<?=$content?>
				<div class="waiver-disclaimer">
			<div>
			<label class="T-Shirt">
				<input type="checkbox" name="have_read" id="have_read" value="1" disabled<?=$confirm_checked?>>
				I HAVE READ THE FOREGOING WAIVER AND RELEASE OF LIABILITY AND VOLUNTARILY EXECUTED THIS DOCUMENT WITH FULL KNOWLEDGE OF ITS CONTENT.
			</label>
			</div>
			<div>
			<label class="T-Shirt">
				<input type="checkbox" name="confirm" id="confirm" value="1" disabled<?=$confirm_checked?>>
				I confirm that I have legal capacity and authorization to act on behalf of the minor listed as User.
			</label>
			</div>
		</div>
		
		</div>
<?php
            $waiver_applies_to = '';
			/*
			$args = array(
			    'post_type'      => 'any',
			    'post_status'    => 'publish',
			    'posts_per_page' => -1,
			    'meta_query'     => array(
			        array(
			            'key'     => 'waiver',
			            'value'   => $post_id,
			            'compare' => '='
			        )
			    )
			);
			
			$events = get_posts($args);
			
			if(!empty($events))
			{
			    foreach ($events as $event) {
					$waiver_applies_to .= '<a href="/etn/' . $event->post_name . '">' . $event->post_title . '</a><br>';
			    }
			}
			
			$waiver_applies_to = substr($waiver_applies_to, 0, strlen($waiver_applies_to) - 4);
			*/
if(0 && $waiver_applies_to)
{
	if($user_role !== 'seller')
	{
?>
		<h4 class="waiver-content_title">Applies to</h4>
		<div class="waiver-content">
			<div class="waiver-disclaimer">
				<div>
				<?=$waiver_applies_to?>
				</div>
			</div>
		</div>
<?php
	}
}
?>
	</div>
<?php
if(0 && $user_role !== 'seller')
{
?>
	<div class="etn-frontend-dashboard profile-data-container" id="profile-data-container">
		<div class="frontend-attendee-list" id="frontend-attendee-list">
		<h4>User Info</h4>
		<div class="Facility_info">
			<div class="edit-post-actions" onclick="expand_block('facility_info')">
				<span id="arrow_facility_info" class="arrow-up" style="display:none;"></span>
			</div>
			<div class="facility_info_subblock ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
				<div class="edit_profile_name-row">
					<div class="ant-form-item css-qgg3xn">
						<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
							<div class="ant-form-item css-qgg3xn">
								<div class="ant-row ant-form-item-row css-qgg3xn">
									<div class="age_group-lable">
										<label for="full_name" class="" title="Full Name">Full Name</label>
									</div>
									<div class="age_group-field">
										<div class="ant-form-item-control-input">
											<div class="ant-form-item-control-input-content">
												<input id="full_name" name="full_name" type="text" placeholder="Full Name" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$full_name?>">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="edit_profile_name-row">
					<div class="ant-form-item css-qgg3xn">
						<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
							<div class="ant-form-item css-qgg3xn">
								<div class="ant-row ant-form-item-row css-qgg3xn">
									<div class="age_group-lable">
										<label for="date_signed" class="" title="Date signed">Date signed</label>
									</div>
									<div class="age_group-field">
										<div class="ant-form-item-control-input">
											<div class="ant-form-item-control-input-content">
												<input id="date_signed" name="date_signed" type="text" placeholder="Date signed" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$date_signed?>">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="facility_info_subblock ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
				<div class="edit_profile_name-row">
					<h4 class="in-case">In case of Emergency Contact:</h4>
					<div class="ant-form-item css-qgg3xn">
						<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
							<div class="ant-form-item css-qgg3xn">
								<div class="ant-row ant-form-item-row css-qgg3xn">
									<div class="age_group-lable">
										<label for="contact_name" class="" title="Contact Name">Contact Name</label>
									</div>
									<div class="age_group-field">
										<div class="ant-form-item-control-input">
											<div class="ant-form-item-control-input-content">
												<input id="contact_name" name="contact_name" type="text" placeholder="Contact Name" class="ant-input css-qgg3xn create_event_input contact_name" value="<?=$contact_name?>">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="edit_profile_name-row">
					<div class="ant-form-item css-qgg3xn">
						<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
							<div class="ant-form-item css-qgg3xn">
								<div class="ant-row ant-form-item-row css-qgg3xn">
									<div class="age_group-lable">
										<label for="contact_phone" class="" title="Contact Phone Number">Contact Phone Number</label>
									</div>
									<div class="age_group-field">
										<div class="ant-form-item-control-input">
											<div class="ant-form-item-control-input-content">
												<input id="contact_phone" name="contact_phone" type="text" placeholder="Contact Phone Number" class="ant-input css-qgg3xn create_event_input contact_phone" value="<?=$contact_phone?>">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>
</div>
<?php
if($user_role == 'seller')
{
	global $wpdb;
	
	$meta_key = 'waiver_' . $post_id;
	$query = $wpdb->prepare("
	    SELECT user_id, meta_value 
	    FROM $wpdb->usermeta 
	    WHERE meta_key = %s
	", $meta_key);
	
	$results = $wpdb->get_results($query);
	
?>
	<style>
	#signed-data-container {
		margin-top: 24px;
	}
	</style>
	<div class="etn-frontend-dashboard" id="signed-data-container">
		<div class="frontend-attendee-list" id="frontend-attendee-list">
		<h4>Signed By</h4>
		<div class="Facility_info">
			<div class="edit-post-actions" onclick="expand_block('facility_info')">
				<span id="arrow_facility_info" class="arrow-up" style="display:none;"></span>
			</div>
			<div class="facility_info_subblock ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
			<table class="waivers-table">
			<thead>
			<tr>
				<th class="waivers-parent-table-th-1" scope="col">Client name</th>
				<th class="waivers-parent-table-th-2" scope="col">Date of sign</th>
				<th class="waivers-parent-table-th-3" scope="col">Emergency Contact Name</th>
				<th class="waivers-parent-table-th-4" scope="col">Emergency Contact number</th>
			</tr>
			</thead>
			<tbody>
<?php
	foreach ($results as $result) {
		//echo 'User ID: ' . $result->user_id . ' - Meta Value: ' . $result->meta_value . '<br>';
		$parent_user = get_user_by('ID', $result->user_id);
		//vd($parent_user->ID);
		if($parent_user)
		{
			$date_time = date('j F, Y h:i A', $result->meta_value);
			$contact_first_name = get_user_meta($result->user_id, 'contact_first_name', TRUE);
			$contact_last_name = get_user_meta($result->user_id, 'contact_last_name', TRUE);
			$contact_phone = get_user_meta($result->user_id, 'contact_phone', TRUE);
			$formatted_phone = '';
			if($contact_phone)
			{
				$contact_phone = preg_replace("/\D/", "", $contact_phone);
				$contact_phone = preg_replace("/(\d{3})(\d{3})(\d{4})/", "$1-$2-$3", $contact_phone);
				$contact_phone = preg_replace("/(\d{3})(\d{3})(\d{4})/", "$1-$2-$3", $contact_phone);
			}
?>
			<tr>
				<td class="waivers-parent-table-td-1"><a href="/edit-profile/<?=$parent_user->ID?>/"><?=$parent_user->display_name?></a></td>
				<td class="waivers-parent-table-td-2"><?=$date_time?></td>
				<td class="waivers-parent-table-td-3"><?=$contact_first_name?> <?=$contact_last_name?></td>
				<td class="waivers-parent-table-td-4"><?=$contact_phone?></td>
			</tr>
<?php
		}
	}
?>
			</tbody>
			</table>
			</div>
		</div>
	</div>
<?php
}
?>
</div>
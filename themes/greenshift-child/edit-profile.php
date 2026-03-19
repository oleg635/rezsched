<?php
$theme_uri = get_stylesheet_directory_uri();
//$user_id = 0;
$display_name = $first_name = $last_name = '';
$description = $email =  '';

$facility_name = $short_bio = $age_group = $level = $total_capacity = 
$room_area = $date_start = $date_end = $time_start = $time_end = '';

$bank_account_name = $routing_number = $bank_account_number = $billing_address = '';

$current_user_id = get_current_user_id();

$can_edit_profile = ($user_id && $user_id == $current_user_id) || current_user_can('manage_options');
$is_seller = $payment_stripe = FALSE;

if($user_id)
{
	$parent_facility = get_user_meta($user_id, 'parent_facility', TRUE);
	
	$dokan_settings = get_user_meta( $user_id, 'dokan_profile_settings', true );
	//vd($dokan_settings, '$dokan_settings');
	$is_seller = (!empty($user_role) && $user_role == 'seller');
	$payment_stripe = !empty($dokan_settings['payment']['stripe']);
	
	$user_info = get_userdata($user_id);
	$email = $user_info->user_email;
	
	$first_name = get_user_meta($user_id, 'first_name', TRUE);
	vd($first_name, '$first_name');
	$last_name = get_user_meta($user_id, 'last_name', TRUE);
	$display_name = $first_name . ' ' . $last_name;
	$phone = get_user_meta($user_id, 'phone', TRUE);
	
	if($parent_facility)
	{
		$facility_name = get_user_meta($parent_facility, 'facility_name', TRUE);
		$short_bio = get_user_meta($parent_facility, 'short_bio', TRUE);
		$street_address_line_1 = get_user_meta($parent_facility, 'street_address_line_1', TRUE);
		$street_address_line_2 = get_user_meta($parent_facility, 'street_address_line_2', TRUE);
		$city = get_user_meta($parent_facility, 'city', TRUE);
		$state = get_user_meta($parent_facility, 'state', TRUE);
		$zip = get_user_meta($parent_facility, 'zip', TRUE);
	}
	else
	{
		$facility_name = get_user_meta($user_id, 'facility_name', TRUE);
		$short_bio = get_user_meta($user_id, 'short_bio', TRUE);
		$street_address_line_1 = get_user_meta($user_id, 'street_address_line_1', TRUE);
		$street_address_line_2 = get_user_meta($user_id, 'street_address_line_2', TRUE);
		$city = get_user_meta($user_id, 'city', TRUE);
		$state = get_user_meta($user_id, 'state', TRUE);
		$zip = get_user_meta($user_id, 'zip', TRUE);
	}
	
	$bank_account_name = get_user_meta($user_id, 'bank_account_name', TRUE);
	$routing_number = get_user_meta($user_id, 'routing_number', TRUE);
	$bank_account_number = get_user_meta($user_id, 'bank_account_number', TRUE);
	$billing_address = get_user_meta($user_id, 'billing_address', TRUE);
	
	$profile_photo_img = '<img decoding="async" src="/wp-content/themes/greenshift-child/img/Avatar.png?' . time() . '" class="gravatar avatar avatar-190 um-avatar um-avatar-uploaded" alt="' . $display_name . '">';
	
	$profile_photo = get_user_meta($user_id, 'profile_photo', TRUE);
	if($profile_photo)
	{
		$profile_photo_url = '/wp-content/uploads/ultimatemember/' . $user_id . '/' . $profile_photo;
		if(is_file($_SERVER['DOCUMENT_ROOT'] . $profile_photo_url))
		{
			$profile_photo_img = '<img decoding="async" src="' . $profile_photo_url . '?' . time() . '" class="gravatar avatar avatar-190 um-avatar um-avatar-uploaded" alt="' . $display_name . '">';
		}
	}
}
?>
	<style>
	#main {
	    padding: 0 !important;
		font-family: 'Open Sans';
		font-style: normal;
		font-weight: 300;
	}
	h1, h2, h3, h4, h5 {
		font-family: 'Open Sans';
	}
	
	.control-button {
		font-weight: 400;
		line-height: 18px;
		text-decoration: none;
		color: #000!important;
		background-color: #5FBFF9!important;
		border: 1px solid #000!important;
		border-radius: 4px!important;
		padding: 14px 23px;
	}
	.control-button-cancel {
		background-color: #fff;
	}
	.control-button:hover {
		box-shadow: 4px 4px 0px 0px #000000;
		color: #000;
	}
	
	.avatar {
		width: 112px;
		height: 112px;
	}
	
	.empty_value {
		border: 1px solid #f00!important;
	}
	</style>
	<!-- script src="https://cdn.tiny.cloud/1/s66maujfpr2mnugwdnoeqhpniiurpa6l91zw4fpyh7edk291/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script -->
	<script src="/ckeditor/ckeditor.js"></script>
	
	<script type="text/javascript">
	var inputs_are_valid = true;
	
	function validate_input(input_id)
	{
		var input = document.getElementById(input_id);
		input.classList.remove('empty_value');
		var val = input.value;
		if(val == '')
		{
			if(inputs_are_valid)
			{
			    jQuery('html, body').animate({
			        scrollTop: jQuery('#' + input_id).offset().top
			    }, 1000);
			}
			
			inputs_are_valid = false;
			input.classList.add('empty_value');
		}
		
		return val;
	}
	
	function validate_area(input_id)
	{
		var input = document.getElementById(input_id);
		input.classList.remove('empty_value');
		
		if(typeof tinymce !== 'undefined')
		{
			var val = tinymce.get(input_id).getContent();
		}
		else if(typeof CKEDITOR !== 'undefined')
		{
			eval('var val = CKEDITOR.instances.' + input_id + '.getData();');
		}
		else
		{
			var val = jQuery('#' + input_id).val();
		}
		
		if(val == '')
		{
			if(inputs_are_valid)
			{
			    jQuery('html, body').animate({
			        scrollTop: jQuery('#' + input_id).offset().top
			    }, 1000);
			}
			
			inputs_are_valid = false;
			input.classList.add('empty_value');
		}
		
		return val;
	}
	
	function prepare_variables()
	{
		inputs_are_valid = true;
<?php
if($parent_facility)
{
?>
		var facility_name = '';
		var short_bio = '';
		var street_address_line_1 = '';
		var street_address_line_2 = '';
		var city = '';
		var state = '';
		var zip = '';
<?php
}
else
{
?>		
		var facility_name = validate_input('facility_name');
		var short_bio = validate_area('short_bio');
		var street_address_line_1 = jQuery('#street_address_line_1').val();
		var street_address_line_2 = jQuery('#street_address_line_2').val();
		var city = jQuery('#city').val();
		var state = jQuery('#state').val();
		var zip = jQuery('#zip').val();
<?php
}
?>		
		
		var first_name = validate_input('first_name');
		var last_name = validate_input('last_name');
		
		var phone = jQuery('#phone').val();
		var email = jQuery('#email').val();
		
		var contact_first_name = jQuery('#contact_first_name').val();
		var contact_last_name = jQuery('#contact_last_name').val();
		var contact_phone = jQuery('#contact_phone').val();
		
		var bank_account_name = jQuery('#bank_account_name').val();
		var routing_number = jQuery('#routing_number').val();
		var bank_account_number = jQuery('#bank_account_number').val();
		var billing_address = jQuery('#billing_address').val();
		
		var password = '';
		var new_password = jQuery('#new_password').val();
		var confirm_password = jQuery('#confirm_password').val();
		
		var new_password_input = document.getElementById('new_password');
		new_password_input.classList.remove('empty_value');
		var confirm_password_input = document.getElementById('confirm_password');
		confirm_password_input.classList.remove('empty_value');
		
		if(new_password !== '' && confirm_password !== '')
		{
			password = validate_input('password');
			if(new_password !== confirm_password)
			{
				if(inputs_are_valid)
				{
				    jQuery('html, body').animate({
				        scrollTop: jQuery('#new_password').offset().top
				    }, 1000);
				}
				
				inputs_are_valid = false;
				new_password_input.classList.add('empty_value');
				confirm_password_input.classList.add('empty_value');
			}
		}
		
		var date_of_birth_user = jQuery('#date_of_birth_user').val();
		var terms_agree_coach_value = '';
		
		var terms_agree_coach = document.getElementById('terms_agree_coach');
		if(terms_agree_coach)
			terms_agree_coach_value = terms_agree_coach.checked ? terms_agree_coach.value : '';		
		
		if(!inputs_are_valid)
			return false;
		
		var post_data = {
				user_id: <?=$user_id?>,
				role: 'Facility/Coach',
				first_name: first_name,
				last_name: last_name,
				phone: phone,
				email: email,
				date_of_birth_user: date_of_birth_user,
				street_address_line_1: street_address_line_1,
				street_address_line_2: street_address_line_2,
				city: city,
				state: state,
				zip: zip,
				facility_name: facility_name,
				short_bio: short_bio,
				
				bank_account_name: bank_account_name,
				routing_number: routing_number,
				bank_account_number: bank_account_number,
				billing_address: billing_address,
				
				password: password,
				new_password: new_password,
				terms_agree_coach: [terms_agree_coach_value],
				security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
			};
		
		console.log('post_data');
		console.log(post_data);
		
		//return false;
		return post_data;
	}
	
	var loading = 0;
	
	function save_post_ajax(event)
	{
		event.preventDefault();
<?php
if($can_edit_profile)
{
?>
		var post_data = prepare_variables();
		if(!post_data)
		{
			return false;
		}
		
		console.log('post_data!');
		console.log(post_data);
		
		jQuery.ajax({
			type: 'POST',
			dataType: 'html',
			url: '<?=$theme_uri?>/wph-edit-profile-ajax.php',
			data: post_data,
			beforeSend : function () {
				waiting(1, 0);
				loading = 1;
			},
			success: function (data)
			{
				waiting(0, 0);
				loading = 0;
				if(data)
				{
					var response = JSON.parse(data);
					console.dir('response!');
					console.dir(response);
					
					if(response.success)
					{
						if(response.error_message)
						{
							if(response.error_message == 'Password has been reset')
								show_system_message(
													'Profile saved', 
													response.error_message, 
													'success',
													'', 
													'',
													'Log In',
													'/sign-in/'
								);
							else
								show_system_message(
													'Profile saved', 
													response.error_message, 
													'success',
													'', 
													'',
													'Close',
													''
								);
						}
						else
						{
							show_system_message(
												'Profile saved', 
												'Your profile has been saved.', 
												'success',
												'', 
												'',
												'Close',
												''
							);
						}
					}
					else
					{
						if(response.error_message)
						{
							show_system_message(
												'Profile not saved', 
												response.error_message, 
												'error',
												'', 
												'',
												'Close',
												''
							);
						}
						else
						{
							show_system_message(
												'Profile not saved', 
												'Your profile has not been saved.', 
												'error',
												'', 
												'',
												'Close',
												''
							);
						}
					}
				}
				else
				{	
				}
			},
			error : function (jqXHR, textStatus, errorThrown) {
				waiting(0, 0);
				alert(errorThrown);
				loading = 0;
				console.log(jqXHR);
			},
		});
		
		return false;
<?php
}
else
{
?>
	show_system_message(
						'Error', 
						'You are not the profile owner', 
						'error',
						'', 
						'',
						'Close',
						''
	);
<?php
}
?>
	}
	</script>
<style>
.edit--avatar{
	width: 112px;
	float: left;
	text-align: center;
}
.edit--avatar a {
	font-size: 16px;
	font-weight: 400;
}
/*
 onclick="javascript:add_services()"
*/

#change_avatar_cancel, #change_avatar_upload {
	display:none;
	margin: auto;
}

#change_avatar_upload {
	padding: 8px;
}


#image-preview {
	width: 112px;
}

#image-preview img {
	width: 112px;
	border-radius: 50%;
}
</style>
<script>
var saved_img = '';
var saved_img_header = '';
function show_upload(show)
{
	if(show)
	{
		document.getElementById('change_avatar_cancel').style.display = 'block';
		document.getElementById('change_avatar_upload').style.display = 'block';
		document.getElementById('change_avatar').style.display = 'none';
		document.getElementById('image').click();
		
	}
	else
	{
		if(saved_img)
		{
			var image_preview = document.getElementById('image-preview');
			image_preview.innerHTML = saved_img;
		}
		
		if(saved_img_header)
		{
			var header_avatar_container = document.getElementById('gspb_col-id-gsbp-2aee450');
			var img = header_avatar_container.querySelector('img');
			console.log('img');
			console.log(img);
			img.setAttribute('src', saved_img_header);

			var img_header = document.getElementById('image-preview');
			img_header.innerHTML = saved_img;
		}
		
		
		document.getElementById('change_avatar_cancel').style.display = 'none';
		document.getElementById('change_avatar_upload').style.display = 'none';
		document.getElementById('change_avatar').style.display = 'block';
	}
}
</script>
<script>
jQuery(document).ready(function($) {
    $('#custom-upload-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('user_id', <?php echo $user_id; ?>);

        $.ajax({
            type: 'POST',
            url: '<?=$theme_uri?>/wph-upload-image-ajax.php',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log('Image uploaded successfully.');
				saved_img = '';
				saved_img_header = '';
				show_upload(false);
            },
            error: function(xhr, status, error) {
                console.error('Error uploading image:', error);
            }
        });
    });

    $('#image').on('change', function(e) {
        var file = e.target.files[0];
        var reader = new FileReader();

        reader.onload = function(e) {
			var image_preview = document.getElementById('image-preview');
			saved_img = image_preview.innerHTML;
			image_preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
			
			var header_avatar_container = document.getElementById('gspb_col-id-gsbp-2aee450');
			var img = header_avatar_container.querySelector('img');
			
			saved_img_header = img.src;
			
			console.log('img');
			console.log(img);
			img.setAttribute('src', e.target.result);
        };

        reader.readAsDataURL(file);
    });
});
</script>
<style>
#image-preview {
	margin: 0 auto;
}
</style>
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
<?php
include('system-message.php');
if($is_seller && !$payment_stripe)
{
?>
<script>
document.querySelectorAll('a').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
		
		show_system_message(
							'Stripe settings', 
							'Please do not forget to connect your stripe account', 
							'success',
							'Connect Stripe', 
							'/dashboard/settings/payment-manage-dokan-stripe-connect/',
							'Close',
							this.href
							);
		
		
    });
});
</script>
<?php
}

if($parent_facility)
{
?>
<style>
.payment_methods
{
	display:none!important;
}
</style>
<?php
}
?>
<div class="edit-profile-container">
	<?php if(wph_is_site_developer()) echo ' <a href="/?switch_user=' . $user_id . '">Switch</a>'; ?>
	<div class="edit--content edit--container edit--avatar">
		<div id="image-preview"><?=$profile_photo_img?></div>
		<div style="clear:both;"></div>
		<a id="change_avatar" onclick="show_upload(true)" href="javascript:void(0)">Edit Image</a>
		<a id="change_avatar_cancel" onclick="show_upload(false)" href="javascript:void(0)">Cancel</a>
		<form id="custom-upload-form" action="#" method="post" enctype="multipart/form-data">
		    <input type="file" name="image" id="image" style="display: none;">
			<input class="control-button" id="change_avatar_upload" type="submit" name="submit" value="Upload">
		</form>
	</div>
	
	<div class="etn-frontend-dashboard profile-data-container" id="profile-data-container">
		<div class="frontend-attendee-list" id="frontend-attendee-list">
		<div class="Facility_info">
			<div class="edit-post-actions" onclick="expand_block('facility_info')">
				<span id="arrow_facility_info" class="arrow-up"></span>
				<h4>Facility Info</h4>
			</div>
			<form id="create_event_form" action="" method="post" onsubmit="javascript:save_post_ajax(event)" autocomplete="on" class="ant-form ant-form-vertical css-qgg3xn">
			<input id="post_status" type="hidden" value="<?=$post_status?>">
<?php
if($parent_facility)
{
?>
			<div class="facility_info_subblock ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
				<div class="edit_profile_name-row">
					<div class="ant-form-item css-qgg3xn">
						<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
							<div class="ant-form-item css-qgg3xn">
								<div class="ant-row ant-form-item-row css-qgg3xn">
									<div class="age_group-lable">
										<label for="facility_name" class="ant-form-item-required" title="Name">Name</label>
									</div>
									<div class="age_group-field">
										<div class="ant-form-item-control-input">
											<div class="ant-form-item-control-input-content">
												<div id="facility_name" name="facility_name" type="text" placeholder="Facility Name" class="ant-input css-qgg3xn create_event_input create_event_input_text"><?=$facility_name?></div>
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
else
{
?>
			<div class="facility_info_subblock ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
				<div class="edit_profile_name-row">
					<div class="ant-form-item css-qgg3xn">
						<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
							<div class="ant-form-item css-qgg3xn">
								<div class="ant-row ant-form-item-row css-qgg3xn">
									<div class="age_group-lable">
										<label for="facility_name" class="ant-form-item-required" title="Name">Name <span class="required_field">*</span></label>
									</div>
									<div class="age_group-field">
										<div class="ant-form-item-control-input">
											<div class="ant-form-item-control-input-content">
												<input id="facility_name" name="facility_name" type="text" placeholder="Facility Name" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$facility_name?>">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="ant-col ant-col-24 css-qgg3xn">
					<div class="ant-form-item css-qgg3xn">
						<div class="ant-row ant-form-item-row css-qgg3xn">
							<div class="ant-col ant-form-item-label css-qgg3xn">
								<label for="short_bio" class="ant-form-item-required" title="Short Bio">Short Bio <span class="required_field">*</span></label>
							</div>
							<div class="ant-col ant-form-item-control css-qgg3xn">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<textarea id="short_bio" name="short_bio"><?=$short_bio?></textarea>
										<script>
										/*
										tinymce.init({
										    selector: '#short_bio',
										    plugins: 'autoresize link lists',
										    toolbar: 'undo redo | formatselect | bold italic | link | bullist numlist',
										    height: 600,
										});
										*/
										CKEDITOR.replace( 'short_bio', {
											height: 300,
											
											filebrowserBrowseUrl: '/ckfinder/ckfinder.html',
											filebrowserImageBrowseUrl: '/ckfinder/ckfinder.html?type=Images',
											filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
											filebrowserImageUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
										});
										</script>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="facility_info_subblock ant-row css-qgg3xn add_new_location">
				<div class="edit-post-actions">
					<h4>Facility Address</h4>
				</div>
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="street_address_line_1" class="ant-form-item-required" title="Street Address Line 1">Street Address Line 1</label>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="street_address_line_1" name="street_address_line_1" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$street_address_line_1?>">
									</div>
								</div>
							</div>
							<div class="street-2-label">
								<label for="street_address_line_2" class="ant-form-item-required" title="Street Address Line 2">Street Address Line 2</label>
							</div>
							<div class="street-2-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="street_address_line_2" name="street_address_line_2" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$street_address_line_2?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="facility_info_subblock ant-row css-qgg3xn add_new_location">
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col-2">
							<div class="city-label">
								<label for="city" class="ant-form-item-required" title="City">City</label>
							</div>
							<div class="city-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="city" name="city" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$city?>">
									</div>
								</div>
							</div>
							<div class="state-label">
								<label for="state" class="ant-form-item-required" title="State">State / Province</label>
							</div>
							<div class="state-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<select name="state" id="state" class="etn_event_select2 etn_event_select etn_event_location">
											<option></option>
<?php
	$states = array(
	    'AL' => 'Alabama',
	    'AK' => 'Alaska',
	    'AZ' => 'Arizona',
	    'AR' => 'Arkansas',
	    'CA' => 'California',
	    'CO' => 'Colorado',
	    'CT' => 'Connecticut',
	    'DE' => 'Delaware',
	    'FL' => 'Florida',
	    'GA' => 'Georgia',
	    'HI' => 'Hawaii',
	    'ID' => 'Idaho',
	    'IL' => 'Illinois',
	    'IN' => 'Indiana',
	    'IA' => 'Iowa',
	    'KS' => 'Kansas',
	    'KY' => 'Kentucky',
	    'LA' => 'Louisiana',
	    'ME' => 'Maine',
	    'MD' => 'Maryland',
	    'MA' => 'Massachusetts',
	    'MI' => 'Michigan',
	    'MN' => 'Minnesota',
	    'MS' => 'Mississippi',
	    'MO' => 'Missouri',
	    'MT' => 'Montana',
	    'NE' => 'Nebraska',
	    'NV' => 'Nevada',
	    'NH' => 'New Hampshire',
	    'NJ' => 'New Jersey',
	    'NM' => 'New Mexico',
	    'NY' => 'New York',
	    'NC' => 'North Carolina',
	    'ND' => 'North Dakota',
	    'OH' => 'Ohio',
	    'OK' => 'Oklahoma',
	    'OR' => 'Oregon',
	    'PA' => 'Pennsylvania',
	    'RI' => 'Rhode Island',
	    'SC' => 'South Carolina',
	    'SD' => 'South Dakota',
	    'TN' => 'Tennessee',
	    'TX' => 'Texas',
	    'UT' => 'Utah',
	    'VT' => 'Vermont',
	    'VA' => 'Virginia',
	    'WA' => 'Washington',
	    'WV' => 'West Virginia',
	    'WI' => 'Wisconsin',
	    'WY' => 'Wyoming'
	);
	
	foreach($states as $state_code => $state_name)
	{
		$selected = $state == $state_code ? ' selected' : '';
?>
											<option value="<?=$state_code?>"<?=$selected?>><?=$state_name?></option>
<?php
	}
	
	$provinces = [
		'AB' => 'Alberta',
		'BC' => 'British Columbia',
		'MB' => 'Manitoba',
		'NB' => 'New Brunswick',
		'NL' => 'Newfoundland and Labrador',
		'NS' => 'Nova Scotia',
		'ON' => 'Ontario',
		'PE' => 'Prince Edward Island',
		'QC' => 'Quebec',
		'SK' => 'Saskatchewan',
	];
	
	foreach($provinces as $state_code => $state_name)
	{
		$selected = $state == $state_code ? ' selected' : '';
?>
											<option value="<?=$state_code?>"<?=$selected?>><?=$state_name?></option>
<?php
	}
?>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="facility_info_subblock ant-row css-qgg3xn add_new_location">
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col-2">
							<div class="zip-label">
								<label for="zip" class="ant-form-item-required" title="Zip">Zip Code / Postal Code</label>
							</div>
							<div class="zip-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="zip" name="zip" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$zip?>">
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
		<div class="personal_info">
			<div class="ant-row css-qgg3xn personal_info-row1">
				<div class="edit-post-actions" onclick="expand_block('personal_info')">
					<span id="arrow_personal_info" class="arrow-up"></span>
					<h4>Personal Info</h4>
				</div>
				<div class="personal_info_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="first_name" class="ant-form-item-required" title="First Name">First Name</label>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="first_name" name="first_name" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$first_name?>">
									</div>
								</div>
							</div>
							<div class="street-2-label">
								<label for="last_name" class="ant-form-item-required" title="Last Name">Last Name</label>
							</div>
							<div class="street-2-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="last_name" name="last_name" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$last_name?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="personal_info_subblock ant-row css-qgg3xn personal_info-row2">
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col-2">
							<div class="city-label">
								<label for="phone" class="ant-form-item-required" title="Phone">Phone</label>
							</div>
							<div class="city-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="phone" name="phone" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$phone?>">
									</div>
								</div>
							</div>
							<div class="state-label">
								<label for="email" class="ant-form-item-required" title="Email">Email</label>
							</div>
							<div class="state-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="email" name="email" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$email?>">
									</div>
								</div>
							</div>

<?php
			$date_birth = get_user_date_birth($user_id);
?>
							<div class="label_date_of_birth">
								<label for="date_of_birth_user" class="ant-form-item-required" title="Date of Birth">Date of Birth</label>
							</div>
							<div class="fields_date_of_birth">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
<?php
			generate_date_selects('user', $date_birth, 'date_of_birth_');
?>
										<input id="date_of_birth_user" name="date_birth" type="hidden" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$date_birth?>">
									</div>
								</div>
							</div>


						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="personal_info payment_methods">
			<div class="ant-row css-qgg3xn security_info-row">
				<div class="edit-post-actions" onclick="expand_block('payment_methods')">
					<span id="arrow_payment_methods" class="arrow-up"></span>
					<h4>Payouts</h4>
				</div>
<?php
	if(1)
	{
		if($can_edit_profile)
		{
			$connect_title = $payment_stripe ? 'Disconnect Stripe Account' : 'Connect Stripe Account';
?>
				<script>
				function connect_stripe_account()
				{
					window.location.href = '/dashboard/settings/payment-manage-dokan-stripe-connect/';
				}
				</script>
				<div class="payment_methods_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<button onclick="connect_stripe_account()" type="button" id="connect_stripe_button" value="Submit" class="etn-btn"><?=$connect_title?></button>
							</div>
						</div>
					</div>
				</div>
<?php
		}
	}
	else
	{
?>
				<div class="payment_methods_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="bank_account_name" class="ant-form-item-required" title="First Name">The name on bank account</label>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="bank_account_name" name="bank_account_name" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$bank_account_name?>">
									</div>
								</div>
							</div>
							<div class="street-2-label">
								<label for="routing_number" class="ant-form-item-required" title="Last Name">Routing Number</label>
							</div>
							<div class="street-2-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="routing_number" name="routing_number" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$routing_number?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="payment_methods_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="bank_account_number" class="ant-form-item-required" title="First Name">Bank Account Number</label>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="bank_account_number" name="bank_account_number" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$bank_account_number?>">
									</div>
								</div>
							</div>
							<div class="street-2-label">
								<label for="billing_address" class="ant-form-item-required" title="Last Name">Billing Address</label>
							</div>
							<div class="street-2-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="billing_address" name="billing_address" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$billing_address?>">
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
		</div>
		
		<div class="security_info">
			<div class="ant-row css-qgg3xn security_info-row">
				<div class="edit-post-actions" onclick="expand_block('security_info')">
					<span id="arrow_security_info" class="arrow-up"></span>
					<h4>Change Password</h4>
				</div>
				<div class="security_info_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="password" class="ant-form-item-required" title="Enter current password">Enter Current Password</label>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="password" name="password" type="password" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
							<div class="street-2-label">
								<label for="new_password" class="ant-form-item-required" title="Enter new password">Enter New Password</label>
							</div>
							<div class="street-2-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="new_password" name="new_password" type="password" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
							<div class="street-3-label">
								<label for="confirm_password" class="ant-form-item-required" title="Enter new password">Re-enter New Password</label>
							</div>
							<div class="street-3-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="confirm_password" name="confirm_password" type="password" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php
	if(0 && $user_id)
	{
		$disabled = '';
		$terms_agree_coach = get_user_meta($user_id, 'terms_agree_coach', TRUE);
		if(!empty($terms_agree_coach))
		{
			$disabled = ' disabled checked';
		}
		vd($terms_agree_coach, '$terms_agree_coach');
?>
		<div class="terms_agreement personal_info" id="terms_agreement">
			<div class="um-field-area">
				<label class="um-field-checkbox  um-field-half "><input type="checkbox" id="terms_agree_coach" name="terms_agree_coach[]" value="I agree to the Rez Terms of Service Agreement"<?=$disabled?>><span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline-blank"></i></span><span class="um-field-checkbox-option">I agree to the Rez <a href="/terms-of-service/">User Service Agreement</a></span></label>
				<div class="um-clear"></div>
			</div>
		</div>
<?php
	}
?>
	</div>
<?php
if($can_edit_profile)
{
?>
	<button type="button" id="create_event_cancel" value="Submit" class="etn-btn">Cancel</button>
	<input type="submit" id="create_event_submit" value="Save" class="etn-btn etn-btn-primary create_event_submit">
<?php
}
?>
	</form>
	<script>
	document.addEventListener('DOMContentLoaded', () => {
	    const create_event_cancel = document.getElementById('create_event_cancel');
	    create_event_cancel.addEventListener('click', function(event) {
			event.preventDefault();
			history.back();
	    });
	});
	</script>
</div>
</div>
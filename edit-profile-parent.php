<?php
$theme_uri = get_stylesheet_directory_uri();

if(!empty($_REQUEST['save_player']) && !empty($_REQUEST['first_name']) && !empty($_REQUEST['last_name']))
	add_new_player();

$display_name = $first_name = $last_name = '';
$profile_photo_img = '<img decoding="async" src="/wp-content/themes/greenshift-child/img/Avatar.png?' . time() . '" class="gravatar avatar avatar-190 um-avatar um-avatar-uploaded" alt="' . $display_name . '">';

$email = $first_name = $last_name = $display_name = $phone = $street_address_line_1 = 
$street_address_line_2 = $city = $state = $zip = $contact_first_name = $contact_last_name = $contact_phone = '';

$current_user_id = get_current_user_id();
$can_edit_profile = ($user_id && $user_id == $current_user_id) || current_user_can('manage_options');

if($user_id)
{
	if(!is_numeric($user_id))
	{
		$user = get_user_by('slug', $user_id);
		$user_id = $user->ID;
	}
	
	$user_info = get_userdata($user_id);
	
	$email = $user_info->user_email;
	
	$first_name = get_user_meta($user_id, 'first_name', TRUE);
	$last_name = get_user_meta($user_id, 'last_name', TRUE);
	$display_name = $first_name . ' ' . $last_name;
	
	$phone = get_user_meta($user_id, 'phone', TRUE);
	
	$street_address_line_1 = get_user_meta($user_id, 'street_address_line_1', TRUE);
	$street_address_line_2 = get_user_meta($user_id, 'street_address_line_2', TRUE);
	$city = get_user_meta($user_id, 'city', TRUE);
	$state = get_user_meta($user_id, 'state', TRUE);
	vd($state, '$state');
	$zip = get_user_meta($user_id, 'zip', TRUE);
	$contact_first_name = get_user_meta($user_id, 'contact_first_name', TRUE);
	$contact_last_name = get_user_meta($user_id, 'contact_last_name', TRUE);
	$contact_phone = get_user_meta($user_id, 'contact_phone', TRUE);
	
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
<?php
include('system-message.php');
?>

<style>
.player_sports {
	display:none;
}

.multiselect-wrapper {
  width: 300px;
  position: relative;
  font-family: sans-serif;
}

.multiselect {
  border: 1px solid #ccc;
  padding: 5px;
  border-radius: 4px;
  min-height: 40px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  cursor: pointer;
}

.selected-options {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  flex: 1;
}

.selected-options .option-tag {
  background-color: #eee;
  padding: 2px 6px;
  border-radius: 3px;
  display: flex;
  align-items: center;
}

.selected-options .option-tag span {
  margin-right: 5px;
}

.option-tag .remove-btn {
  cursor: pointer;
  font-weight: bold;
}

.dropdown-arrow {
  margin-left: auto;
  padding-left: 5px;
}

.options-dropdown {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 10;
  background: white;
  border: 1px solid #ccc;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
  border-radius: 0 0 4px 4px;
}

.options-dropdown label {
  display: block;
  padding: 5px 10px;
  cursor: pointer;
}

.options-dropdown label:hover {
  background: #f0f0f0;
}
</style>

<?php
if(!empty($_REQUEST['player_name']))
{
?>
	<script>
	document.addEventListener("DOMContentLoaded", function() {
		const element = document.querySelector('[data-player_name="<?=$_REQUEST['player_name']?>"]');
		if (element) {
		  element.scrollIntoView({ behavior: 'smooth' });
		}
	});
	</script>
<?php
}
elseif(!empty($_REQUEST['save_player']) && !empty($_REQUEST['first_name']) && !empty($_REQUEST['last_name']))
{
?>
	<script>
	document.addEventListener("DOMContentLoaded", function() {
		const element = document.querySelector('[data-player_name="<?=$_REQUEST['first_name'][0]?> <?=$_REQUEST['last_name'][0]?>"]');
		if (element) {
		  element.scrollIntoView({ behavior: 'smooth' });
		}
	});
	</script>
<?php
}
?>
	<style>
	.d-none {
		display:none;
	}
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
	<script type="text/javascript">
	function init_multiselects()
	{
		document.querySelectorAll('.multiselect-wrapper').forEach(wrapper => {
		  const toggle = wrapper.querySelector('.multiselect');
		  const dropdown = wrapper.querySelector('.options-dropdown');
		  const selectedContainer = wrapper.querySelector('.selected-options');
		  const checkboxes = wrapper.querySelectorAll('input[type="checkbox"]');
		
		  // Toggle dropdown
		  toggle.addEventListener('click', () => {
		    const isOpen = dropdown.style.display === 'block';
		    document.querySelectorAll('.options-dropdown').forEach(d => d.style.display = 'none'); // close all others
		    dropdown.style.display = isOpen ? 'none' : 'block';
		  });
		
		  // Click outside to close
		  document.addEventListener('click', (e) => {
		    if (!wrapper.contains(e.target)) {
		      dropdown.style.display = 'none';
		    }
		  });
		
		  // Handle checkbox changes
		  checkboxes.forEach(checkbox => {
		    checkbox.addEventListener('change', () => {
		      const value = checkbox.value;
			  const label = checkbox.dataset.label || value;
		      if (checkbox.checked) {
		        if (!selectedContainer.querySelector(`[data-value="${value}"]`)) {
		          const tag = document.createElement('div');
		          tag.className = 'option-tag';
		          tag.dataset.value = value;
		          tag.innerHTML = `<span>${label}</span><!-- span class="remove-btn">&times;</span -->`;
		          //tag.querySelector('.remove-btn').addEventListener('click', () => {
		            //checkbox.checked = false;
		            //tag.remove();
		          //});
		          selectedContainer.appendChild(tag);
		        }
		      } else {
		        const tag = selectedContainer.querySelector(`[data-value="${value}"]`);
		        if (tag) tag.remove();
		      }
		    });
		  });
		});
	}
	
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
				console.log('validate_input input_id');
				console.log(input_id);
			    jQuery('html, body').animate({
			        scrollTop: jQuery('#' + input_id).offset().top - 48
			    }, 1000);
			}
			
			inputs_are_valid = false;
			input.classList.add('empty_value');
		}
		
		return val;
	}
	
	function isAnySportChecked(child_id) {
	  return jQuery('input[name="sports_child_' + child_id + '[]"]:checked').length > 0;
	}
	
	function validate_multiselect(child_id)
	{
		var input_id = 'sports_' + child_id;
		//alert(input_id);
		var input = document.getElementById(input_id);
		input.classList.remove('empty_value');
		
		var val = jQuery('input[name="sports_child_' + child_id + '[]"]:checked')
			  .map(function() { return this.value; })
			  .get()
			  .join(',');

		if(!val)
		{
			if(inputs_are_valid)
			{
				console.log('validate_input input_id');
				console.log(child_id);
				console.log('input');
				console.log(input);
			    jQuery('html, body').animate({
			        scrollTop: jQuery('#' + input_id).offset().top - 48
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
		else
		{
			var short_bio = jQuery('#' + input_id).val();
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
		
		var first_name = validate_input('first_name');
		var last_name = validate_input('last_name');
		
		var street_address_line_1 = validate_input('street_address_line_1');
		var street_address_line_2 = jQuery('#street_address_line_2').val();
		var city = validate_input('city');
		var state = validate_input('state');
		var zip = validate_input('zip');
		
		var phone = jQuery('#phone').val();
		var email = validate_input('email');
		
		var contact_first_name = jQuery('#contact_first_name').val();
		var contact_last_name = jQuery('#contact_last_name').val();
		var contact_phone = jQuery('#contact_phone').val();
		
		var password = jQuery('#password').val();
		var new_password = jQuery('#new_password').val();
		var confirm_password = jQuery('#confirm_password').val();
		
		var new_password_input = document.getElementById('new_password');
		new_password_input.classList.remove('empty_value');
		var confirm_password_input = document.getElementById('confirm_password');
		confirm_password_input.classList.remove('empty_value');
		
		var i_am_a_player_checkbox = document.getElementById('i_am_a_player');
		if(i_am_a_player_checkbox && i_am_a_player_checkbox.checked)
		{
			i_am_a_player = 1;
			
			validate_input('dayuser');
			validate_input('monthuser');
			validate_input('yearuser');
			var sports_user = '';
			//var sports_user = validate_multiselect(<?=$user_id?>);
			var date_of_birth_user = jQuery('#date_of_birth_user').val();
			var gender_user = validate_input('gender');
		}
		else
		{
			i_am_a_player = 0;
			
			var date_of_birth_user = jQuery('#date_of_birth_user').val();
			var sports_user = '';
			var gender_user = jQuery('#gender').val();
		}
		
		if(new_password !== '' && confirm_password !== '')
		{
<?php
	if($user_id)
	{
?>
			password = validate_input('password');
<?php
	}
?>
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
<?php
	if(!$user_id)
	{
?>
			password = new_password;
<?php
	}
?>
		}
		
		var children = [];
		
		var first_name_child;
		var last_name_child;
		var date_of_birth_child;
		var day_child;
		var month_child;
		var year_child;
		var gender_child;
		var child_id;
		var is_sport_selected;
		var sports_child;

		var children_ids = document.querySelectorAll('.child_id');
		console.log('children_ids');
		console.log(children_ids);
		for(var i = 0; i < children_ids.length; i++)
		{
			console.log('children_ids[' + i + '].value');
			console.log(children_ids[i].value);
			child_id = parseInt(children_ids[i].value);
			
			if(child_id !== 0)
			{
				first_name_child = validate_input('first_name_child_' + child_id);
				last_name_child = validate_input('last_name_child_' + child_id);
				sports_child = '';
				//sports_child = validate_multiselect(child_id);
				day_child = validate_input('day' + child_id);
				month_child = validate_input('month' + child_id);
				year_child = validate_input('year' + child_id);
				date_of_birth_child = jQuery('#date_of_birth_child_' + child_id).val();
				gender_child = validate_input('gender_child_' + child_id);
			}
			else
			{
				first_name_child = jQuery('#first_name_child_' + child_id).val();
				last_name_child = jQuery('#last_name_child_' + child_id).val();
				date_of_birth_child = jQuery('#date_of_birth_child_' + child_id).val();
				gender_child = jQuery('#gender_child_' + child_id).val();
				
				var sports_child = jQuery('input[name="sports_child_' + child_id + '[]"]:checked')
					  .map(function() { return this.value; })
					  .get()
					  .join(',');
				
				console.log('first_name_child');
				console.log(first_name_child);
				console.log('last_name_child');
				console.log(last_name_child);
				console.log('date_of_birth_child');
				console.log(date_of_birth_child);
				console.log('gender_child');
				console.log(gender_child);
				console.log('sports_child');
				console.log(sports_child);
				
				//if(first_name_child !== '' || last_name_child !== '' || date_of_birth_child !== '' || gender_child !== '' || sports_child !== '')
				if(first_name_child !== '' || last_name_child !== '' || date_of_birth_child !== '' || gender_child !== '')
				{
					first_name_child = validate_input('first_name_child_' + child_id);
					last_name_child = validate_input('last_name_child_' + child_id);
					day_child = validate_input('day' + child_id);
					month_child = validate_input('month' + child_id);
					year_child = validate_input('year' + child_id);
					date_of_birth_child = jQuery('#date_of_birth_child_' + child_id).val();
					//sports_child = validate_multiselect(child_id);
					gender_child = validate_input('gender_child_' + child_id);
				}
			}
			
			
				console.log('inputs_are_valid');
				console.log(inputs_are_valid);
			
			children[i] = {
							id: child_id,
							first_name: first_name_child,
							last_name: last_name_child,
							date_of_birth: date_of_birth_child,
							sports: sports_child,
							current_team: jQuery('#current_team_child_' + child_id).val(),
							level: jQuery('#level_child_' + child_id).val(),
							gender: gender_child,
							delete_child: jQuery('#delete_child_' + child_id).val(),
						};
		}
		
		console.log('children');
		console.log(children);
		
		var terms_agree_parent = document.getElementById('terms_agree_parent');
		
		if(terms_agree_parent)
		{
			var terms_agree_parent_value = terms_agree_parent.checked ? terms_agree_parent.value : '';		
		}
		
		if(!inputs_are_valid)
		{
			return false;
		}
		
		var post_data = {
				user_id: <?=$user_id?>,
				role: '<?=$user_role?>',
				first_name: first_name,
				last_name: last_name,
				phone: phone,
				email: email,
				i_am_a_player: i_am_a_player,
				date_of_birth_user: date_of_birth_user,
				sports: sports_user,
				gender: gender_user,
				street_address_line_1: street_address_line_1,
				street_address_line_2: street_address_line_2,
				city: city,
				state: state,
				zip: zip,
				contact_first_name: contact_first_name,
				contact_last_name: contact_last_name,
				contact_phone: contact_phone,
				password: password,
				new_password: new_password,
				children: children,
				terms_agree_parent: [terms_agree_parent_value],
				security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
			};
		
		console.log('post_data');
		console.log(post_data);
		
		return post_data;
	}
	
	var loading = 0;
	
	function save_post_ajax(event)
	{
		event.preventDefault();
		var post_data = prepare_variables();
		
		if(!post_data)
		{
			event.preventDefault();
			return false;
		}
		
		console.log('post_data');
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
					console.dir('response');
					console.dir(response);
					
					if(response.success)
					{
						if(typeof response.children_html !== 'undefined')
						{
							var children_forms = document.getElementById('children_forms');
							if(children_forms)
								children_forms.innerHTML = response.children_html;
								
							init_multiselects();
						}
						
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
<?php
		if(!$user_id)
		{
?>
													'/edit-profile/' + response.user_id
<?php
		}
		else
		{
?>
													''
<?php
		}
?>
								);
						}
						else
						{
<?php
		if(!empty($_GET['redirect']))
		{
?>
							window.location.href='<?=$_GET['redirect']?>';
							/*
							show_system_message(
												'Profile saved', 
												'Your profile has been saved.',
												'success',
												'Go to the event page', 
												'<?=$_GET['redirect']?>',
												'Close',
												''
							);
							*/
<?php
		}
		else
		{
?>
							show_system_message(
												'Profile saved', 
												'Your profile has been saved.',
												'success',
												'', 
												'',
												'Close',
												''
							);
<?php
		}
?>
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
				loading = 0;
				console.log(jqXHR);
			},
		});
		
		return false;
	}
	</script>

<style>
#add_player_button {
    border-radius: 12px;
    border: 1px solid var(--Accent-color-100, #17A0B2);
    background: transparent;
    color: var(--Accent-color-100, #17A0B2);
    font-family: "DM Sans";
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 12px;
    padding: 0 12px 12px 12px;
    cursor: pointer;
    margin-left: auto;
	text-decoration-line: none;
}

#add_player_button:after {
    filter: invert(61%) sepia(31%) saturate(2979%) hue-rotate(152deg) brightness(98%) contrast(97%);
    content: url(https://rezbeta.wpenginepowered.com/wp-content/uploads/2024/04/Add_Plus.svg);
    position: relative;
    top: 7px;
    left: 4px;
}

.new_player {
	display:none;
}

.last_block {
	border-bottom: 1px solid #a8b5c2;
    margin-bottom: 12px;
}

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
	font-size: 24px;
	line-height: 1.4;
	font-weight: 700;
	margin: 0 0 12px 0;
}
.add--link-fields label {
	display: block;
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
        formData.append('user_id', <?=$user_id?>);

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

function toggle_children()
{
	var children_forms = document.getElementById('children_forms');
	if(children_forms.style.display == 'none')
	{
		children_forms.style.display = 'block';
	    jQuery('html, body').animate({
	        scrollTop: jQuery('#children_forms').offset().top
	    }, 1000);
	}
	else
	{
		children_forms.style.display = 'none';
	}
}
</script>
<style>
.edit-profile-header {
	margin: 0 0 48px 0;
}
.edit-profile-header h2 {
	display: inline-block;
	margin: 0;
}
.edit-profile-header button {
	float: right;
}
</style>
<div class="edit-profile-header">
	<h2><?=$display_name?></h2><?php if(wph_is_site_developer()) echo ' <a href="/?switch_user=' . $user_id . '">Switch</a>'; ?>
	<button id="add_children" onclick="toggle_children()" value="Add Child" class="etn-btn add_children">Add Player/Child</button>
</div>
<div class="edit-profile-container">
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
	<!-- button onclick="show_system_message('Account Created.', 'Your Account has been created. Take next action from the below button.', 'success')">test popup success</button>
	<button onclick="show_system_message('Account hasn\'t been created', '[span]Error![/span] Your Account has not been created. Go back to the Account and check if everything is filled in correctly.', 'error')">test popup error</button -->
		<div class="frontend-attendee-list" id="frontend-attendee-list">
		<div class="personal_info">
			<form id="create_event_form" action="" method="post" onsubmit="javascript:save_post_ajax(event)" autocomplete="on" class="ant-form ant-form-vertical css-qgg3xn">
			<div class="ant-row css-qgg3xn personal_info-row1">
				<div class="edit-post-actions" onclick="expand_block('personal_info')">
					<span id="arrow_personal_info" class="arrow-up"></span>
					<h4>Personal Info</h4>
				</div>
				<div class="personal_info_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="first_name" class="ant-form-item-required" title="First Name">First Name</label> <span class="required_field">*</span>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="first_name" name="first_name" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$first_name?>">
									</div>
								</div>
							</div>
							<div class="street-2-label">
								<label for="last_name" class="ant-form-item-required" title="Last Name">Last Name</label> <span class="required_field">*</span>
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
								<label for="email" class="ant-form-item-required" title="Email">Email</label> <span class="required_field">*</span>
							</div>
							<div class="state-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="email" name="email" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" autocomplete="off" value="<?=$email?>">
									</div>
								</div>
							</div>
<?php
			$i_am_a_player = get_user_meta($user_id, 'i_am_a_player', TRUE);
			
			if($i_am_a_player)
			{
				$checked = ' checked';
				$player_feature_style = '';
			}
			else
			{
				$checked = '';
				$player_feature_style = 'display:none;';
			}
?>
							<script>
							function handle_i_am_a_player()
							{
								var i_am_a_player_checkbox = document.getElementById('i_am_a_player');
								if(i_am_a_player_checkbox && i_am_a_player_checkbox.checked)
								{
									document.querySelectorAll('.player_feature').forEach(el => {
										if (!el.classList.contains('player_sports'))
											el.style.display = 'block';
									});
								}
								else
								{
									document.querySelectorAll('.player_feature').forEach(el => {
										if (!el.classList.contains('player_sports'))
											el.style.display = 'none';
									});
								}
							}
							</script>
							<div class="label_date_of_birth">
								<label onclick="handle_i_am_a_player()" class="ant-form-item-required"><input type="checkbox" id="i_am_a_player" name="i_am_a_player" value="1"<?=$checked?>>I am a player</label>
								<div class="um-clear"></div>
							</div>
							<div>
<?php
			$date_birth = get_user_date_birth($user_id);
?>
								<div class="label_date_of_birth player_feature" style="<?=$player_feature_style?>">
									<label for="date_of_birth_user" class="ant-form-item-required" title="Date of Birth">Date of Birth</label> <span class="required_field">*</span>
								</div>
								<div class="fields_date_of_birth player_feature" style="<?=$player_feature_style?> margin-top:12px;">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
<?php
			generate_date_selects('user', $date_birth, 'date_of_birth_');
?>
											<input id="date_of_birth_user" name="date_birth" type="hidden" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$date_birth?>">
										</div>
									</div>
								</div>
								<div class="label_date_of_birth player_feature player_sports" style="<?=$player_feature_style?>">
									<label for="sports_<?=$user_id?>" class="ant-form-item-required" title="Sports">Sports / Interests</label> <span class="required_field">*</span>
								</div>
								<div class="street-2-input player_feature player_sports" style="<?=$player_feature_style?>">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
<?php
			$activity_types = get_all_activity_types();
			$user_sports = get_user_meta($user_id, 'sports', TRUE);
			
			$selected_values = explode(',', $user_sports);
			render_activity_multiselect($activity_types, $user_id, $selected_values);
?>
										</div>
									</div>
								</div>
								<div class="label_date_of_birth player_feature" style="<?=$player_feature_style?>">
									<label for="gender" class="ant-form-item-required" title="Gender">Gender</label> <span class="required_field">*</span>
								</div>
<?php
				$gender = get_user_meta($user_id, 'gender', TRUE);
?>
								<div class="street-1-input player_feature" style="<?=$player_feature_style?>">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<select name="gender" id="gender" class="gender etn_event_select2 etn_event_select etn_event_location">
												<option></option>
<?php
				$genders = array(
				    'Male' => 'Male',
				    'Female' => 'Female',
				    'Other / Non-binary' => 'Other / Non-binary',
				    'Prefer not to disclose' => 'Prefer not to disclose',
				);
				
				foreach($genders as $gender_code => $gender_name)
				{
					$selected = $gender == $gender_code ? ' selected' : '';
?>
												<option value="<?=$gender_code?>"<?=$selected?>><?=$gender_name?></option>
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
			</div>

			<div class="personal_info_subblock ant-row css-qgg3xn add_new_location facility_address">
				<div class="edit-post-actions">
					<h4>Address</h4>
				</div>
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="street_address_line_1" class="ant-form-item-required" title="Street Address Line 1">Street Address Line 1 <span class="required_field">*</span></label>
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
			<div class="personal_info_subblock ant-row css-qgg3xn add_new_location">
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col-2">
							<div class="city-label">
								<label for="city" class="ant-form-item-required" title="City">City <span class="required_field">*</span></label>
							</div>
							<div class="city-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="city" name="city" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$city?>">
									</div>
								</div>
							</div>
							<div class="state-label">
								<label for="state" class="ant-form-item-required" title="State">State / Province <span class="required_field">*</span></label>
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

if(in_array($state, $states))
	$state = array_search($state, $states);

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
			<div class="personal_info_subblock ant-row css-qgg3xn add_new_location">
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col-2">
							<div class="zip-label">
								<label for="zip" class="ant-form-item-required" title="Zip">Zip Code / Postal Code <span class="required_field">*</span></label>
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
			
			<div class="personal_info_subblock ant-row css-qgg3xn add_new_location emergency_contact">
				<div class="edit-post-actions">
					<h4>Emergency Contact</h4>
				</div>
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="contact_first_name" class="ant-form-item-required" title="First name">First Name</label>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="contact_first_name" name="contact_first_name" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text contact_first_name" value="<?=$contact_first_name?>">
									</div>
								</div>
							</div>
							<div class="street-2-label">
								<label for="contact_last_name" class="ant-form-item-required" title="Last name">Last Name</label>
							</div>
							<div class="street-2-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="contact_last_name" name="contact_last_name" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text contact_last_name" value="<?=$contact_last_name?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="personal_info_subblock ant-row css-qgg3xn add_new_location">
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col-2">
							<div class="zip-label">
								<label for="contact_phone" class="ant-form-item-required" title="Phone number">Phone Number</label>
							</div>
							<div class="zip-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="contact_phone" name="contact_phone" type="text" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$contact_phone?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
		</div>

<?php
	if(!$user_id)
	{
?>
		<div class="security_info">
			<div class="ant-row css-qgg3xn security_info-row">
				<div class="edit-post-actions" onclick="expand_block('security_info')">
					<span id="arrow_security_info" class="arrow-up"></span>
					<h4>Add Password</h4>
				</div>
				<div class="security_info_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="password" class="ant-form-item-required" title="Enter current password">Set Password</label>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="password" name="password" type="password" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
							<div class="street-2-label d-none">
								<label for="new_password" class="ant-form-item-required" title="Enter new password">Enter new password</label>
							</div>
							<div class="street-2-input d-none">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="new_password" name="new_password" type="password" placeholder="" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
							<div class="street-3-label d-none">
								<label for="confirm_password" class="ant-form-item-required" title="Enter new password">Re-enter new password</label>
							</div>
							<div class="street-3-input d-none">
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
	}
	else
	{
?>
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
		<style>
		.card-actions {
			margin-left: auto;		
		}
		#add_card_button {
		    border-radius: 12px;
		    border: 1px solid var(--Accent-color-100, #17A0B2);
		    background: transparent;
		    color: var(--Accent-color-100, #17A0B2);
		    font-family: "DM Sans";
		    font-size: 14px;
		    font-style: normal;
		    font-weight: 400;
		    line-height: 12px;
		    padding: 0 12px 12px 12px;
		    cursor: pointer;
			margin-left: auto;
		}
		#add_card_button:after {
            filter: invert(61%) sepia(31%) saturate(2979%) hue-rotate(152deg) brightness(98%) contrast(97%);
		    content: url(https://rezbeta.wpenginepowered.com/wp-content/uploads/2024/04/Add_Plus.svg);
		    position: relative;
		    top: 7px;
		    left: 4px;
		}		
		</style>
<?php
		$saved_methods = wc_get_customer_saved_methods_list($user_id);
		$has_methods   = (bool) $saved_methods;
		do_action( 'woocommerce_before_account_payment_methods', $has_methods );
?>
		<div id="payment_methods" class="personal_info payment_methods">
			<div class="ant-row css-qgg3xn security_info-row">
				<div class="edit-post-actions">
					<span id="arrow_payment_methods" class="arrow-up" onclick="expand_block('payment_methods')"></span>
					<h4>Payment</h4>
					<div class="ant-form-item-control-input-content card-actions">
						<button type="button" id="add_card_button" value="Add Card" onclick="javascript:add_card()">Add Card</button>
					</div>
				</div>
				<div class="payment_methods_subblock ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
				<table class="payment_methods_table">
					<tbody>

<?php
		if($has_methods)
		{
			$index = 1;
			$user_param = '';
			if($user_id !== get_current_user_id())
				$user_param = '&user_id=' . $user_id;
			foreach($saved_methods as $type => $methods)
			{
				foreach($methods as $method)
				{
					$method_id = 0;
			        if ( isset( $method['actions']['delete']['url'] ) ) {
			            $url = $method['actions']['delete']['url'];
			            $parts = explode('/', rtrim($url, '/'));
			            $method_id = $parts[count($parts) - 2];
						
						$delete_url = wp_nonce_url( home_url( 'custom-delete-payment-method/' . $method_id ), 'woocommerce-delete-payment-method' );
						$delete_url .= $user_param;
						
						$method['actions']['delete']['url'] = $delete_url;
			        }
					
					$custom_set_default_url = wp_nonce_url( home_url( 'custom-set-default-payment-method/' . $method_id ), 'woocommerce-set-default-payment-method' );
					$custom_set_default_url .= $user_param;
					
					$delete_button = !empty($method['actions']['delete']['url']) ? 
					'<button type="button" onclick="window.location.href=\'' . $method['actions']['delete']['url'] . '\'" id="delete_' . $index . '" value="Delete" class="etn-btn action_button">Delete</button>' : '';
					
					$default_button = !empty($method['actions']['default']['url']) ? 
					'<label class="use_as_primary_label"><input class="use_as_primary" id="use_as_primary_' . $index . '" type="checkbox" onclick="set_default(this, \'' . $custom_set_default_url . '\')" value="1">Use as primary</label>' : '<label class="use_as_primary_label"><input class="use_as_primary" id="use_as_primary_' . $index . '" onclick="set_default(this, \'\')" type="checkbox" name="use_as_primary" value="1" checked>Use as primary</label>';
					
					$brand = wc_get_credit_card_type_label($method['method']['brand']);
?>
					<tr>
						<td class="payment-methods-table-td-1 table-title"><img src="/wp-content/themes/greenshift-child/img/<?=strtolower($brand)?>.png" width="110" height="81" alt="" border="0"></td>
						<td class="payment-methods-table-td-2 table-title"><span class="card_number"><?=$brand?> ****<?php if(!empty($method['method']['last4'])) echo $method['method']['last4']; ?></span>&nbsp;&nbsp;<span class="card_expires">Expires <?php echo esc_html( $method['expires'] );?></span><!-- br>Marley Kenter --></td>
						<td class="payment-methods-table-td-3 table-title"><?=$default_button?></td>
						<td class="payment-methods-table-td-4 table-title"><a href="#">Learn more</a></td>
						<td class="payment-methods-table-td-5 table-title"><?=$delete_button?></td>
					</tr>
<?php
				}
			}
		}
		else
		{
			wc_print_notice( esc_html__( 'No saved methods found.', 'woocommerce' ), 'notice' );
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
		<script>
		function add_card()
		{
<?php
if(!$user_id)
{
?>
			show_system_message(
								'Profile not saved', 
								'Please save the profile first.', 
								'error',
								'', 
								'',
								'Close',
								''
			);
			
			return;
<?php
}
?>
			window.location.href = '/add-payment-method/<?php if($user_id !== get_current_user_id()) echo $user_id; ?>';
		}
		
		function set_default(checkbox, url)
		{
			var checkboxes = document.querySelectorAll('.use_as_primary');
			for(var i = 0; i < checkboxes.length; i++)
				checkboxes[i].checked = false;
			
			checkbox.checked = true;
			
			if(url)
				window.location.href = url;
		}
		</script>
<?php
function display_saved_cards($user_id) {
?>
		<div class="personal_info payment_methods">
			<div class="ant-row css-qgg3xn security_info-row">
				<div class="edit-post-actions">
					<span id="arrow_payment_methods" class="arrow-up" onclick="expand_block('payment_methods')"></span>
					<h4>Payment</h4>
					<div class="ant-form-item-control-input-content card-actions">
						<button type="button" id="add_card_button" value="Add Card" onclick="javascript:add_card()">Add Card</button>
					</div>
				</div>
				<div class="payment_methods_subblock ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
				<table class="payment_methods_table">
					<tbody>
<?php
    if($user_id) {
        $payment_methods = WC_Payment_Tokens::get_customer_tokens($user_id);
		
        if (!empty($payment_methods)) {
            foreach ($payment_methods as $payment_method) {
				$get_type = $payment_method->get_type();
				
                if ($payment_method->get_type() === 'CC') {
                    $card_type = $payment_method->get_card_type();
                    $last4 = $payment_method->get_last4();
                    $exp_month = $payment_method->get_expiry_month();
                    $exp_year = $payment_method->get_expiry_year();
?>
					<tr>
<?php
					if($card_type == 'visa')
					{
?>
						<td class="payment-methods-table-td-1 table-title"><img src="/wp-content/themes/greenshift-child/img/visa.png" width="110" height="81" alt="" border="0"></td>
						<td class="payment-methods-table-td-2 table-title"><span class="card_number">Visa ****<?=$last4?></span>&nbsp;&nbsp;<span class="card_expires">Expires <?=$exp_month?>/<?=$exp_year?></span><!-- br>Marley Kenter --></td>
<?php
					}
					else
					{
?>
						<td class="payment-methods-table-td-1 table-title"><img src="/wp-content/themes/greenshift-child/img/mastercard.png" width="110" height="81" alt="" border="0"></td>
						<td class="payment-methods-table-td-2 table-title"><span class="card_number">Mastercard ****<?=$last4?></span>&nbsp;&nbsp;<span class="card_expires">Expires <?=$exp_month?>/<?=$exp_year?></span><br>Marley Kenter</td>
<?php
					}
?>
						<td class="payment-methods-table-td-3 table-title"><label class="use_as_primary_label"><input type="checkbox" name="use_as_primary" id="use_as_primary_1" value="1" checked>Use as primary</label></td>
						<td class="payment-methods-table-td-4 table-title"><a href="#">Learn more</a></td>
						<td class="payment-methods-table-td-5 table-title"><button type="button" id="delete_1" value="Delete" class="etn-btn action_button">Delete</button></td>
					</tr>
<?php
                }


            }
        }
		else
		{
?>
					<tr>
						<td colspan="5" class="payment-methods-table-td-1 table-title">No saved cards.</td>
					</tr>
<?php
        }
    }
	else
	{
?>
					<tr>
						<td colspan="5" class="payment-methods-table-td-1 table-title">No user logged in.</td>
					</tr>
<?php
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
		<script>
		function toggle_children()
		{
		    jQuery('html, body').animate({
		        scrollTop: jQuery('#children_forms').offset().top
		    }, 1000);
			
			return;
			
			var children_forms = document.getElementById('children_forms');
			if(children_forms.style.display == 'none')
			{
				children_forms.style.display = 'block';
			    jQuery('html, body').animate({
			        scrollTop: jQuery('#children_forms').offset().top
			    }, 1000);
			}
			else
			{
				children_forms.style.display = 'none';
			}
		}
		
		function add_player()
		{
			var new_player = document.getElementById('new_player');
			new_player.style.display = 'block';
			jQuery('.new_player').css('display:block');
			
			var new_player_blocks = document.querySelectorAll('.new_player');
			for(var i = 0; i < new_player_blocks.length; i++)
				new_player_blocks[i].style.display = 'block';
			
			var last_block_no_border = document.querySelector('.last_block_no_border');
			last_block_no_border.classList.add('last_block');
			
		    jQuery('html, body').animate({
		        scrollTop: jQuery('#new_player').offset().top
		    }, 1000);
		}
		
	    function delete_child(child_id) {
	        var delete_child_input = jQuery("#delete_child_" + child_id);
			delete_child_input.val(child_id);
	        var child_container = jQuery("#child_container_" + child_id);
	        
	        // Fade out the container and then slide it up
	        child_container.fadeOut(400, function() {
	            child_container.slideUp(400, function() {
					child_container.remove(); // Finally remove the container from the DOM
	            });
	        });
	    }
		</script>
		
<?php
	if($user_id)
	{
?>
		<div class="my_children personal_info" id="children_forms">
<?php
		include('edit-profile-parent-children.php');
		
		if(0)
		{
			$disabled = '';
			$terms_agree_parent = get_user_meta($user_id, 'terms_agree_parent', TRUE);
			if(!empty($terms_agree_parent))
			{
				$disabled = ' disabled checked';
			}
			vd($terms_agree_parent, '$terms_agree_parent');
?>
		</div>
		<div class="terms_agreement personal_info" id="terms_agreement">
			<div class="um-field-area">
				<label class="um-field-checkbox  um-field-half "><input type="checkbox" id="terms_agree_parent" name="terms_agree_parent[]" value="I agree to the Rez Terms of Service Agreement"<?=$disabled?>><span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline-blank"></i></span><span class="um-field-checkbox-option">I agree to the Rez <a href="/user-service-agreement/">User Service Agreement</a></span></label>
				<div class="um-clear"></div>
			</div>
		</div>
<?php
		}
	}
?>

	</div>
<?php
if($can_edit_profile)
{
?>
	<button type="button" id="create_event_cancel" onclick="javascript:history.back();" value="Cancel" class="etn-btn">Cancel</button>
	<input type="submit" id="create_event_submit" value="Save" class="etn-btn etn-btn-primary create_event_submit">
<?php
}
?>
	</form>
</div>
</div>
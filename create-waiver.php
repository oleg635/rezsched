<?php
	$current_user_id = get_current_user_id();
	$service_ids = $post_title = $post_content = $post_status = '';
	$waiver_post = $waiver_id = $ID = 0;
	
	if(!empty($_REQUEST['id']) && $waiver_post = get_post($_REQUEST['id']))
	{
		$waiver_id = intval($_REQUEST['id']);
		
		$post_title = $waiver_post->post_title;
		$post_content = $waiver_post->post_content;
		
		$post_status = $waiver_post->post_status;
	}
?>
<?php
include('system-message.php');
?>
<div class="etn-frontend-dashboard create_event_container" id="create_event_container">
	<div class="frontend-attendee-list" id="frontend-attendee-list">
		<div class="edit-post-actions">
			<h4>Add Waiver</h4>
		<div class="create_waiver-buttons">
			<button class="discard-btn_waiver" onclick="discard_changes(event)">Discard Changes</button>
			<button id="button_save_draft" class="save-btn_waiver" onclick="save_post_draft(event)">Save as Draft</button>
			<button id="button_save_publish" class="publish-btn_waiver etn-btn-primary" onclick="save_post_publish(event)">Save and Publish</button>
		</div>
		</div>
		<form id="create_event_form" action="" method="post" onsubmit="javascript:save_post_publish(event)" autocomplete="on" class="ant-form ant-form-vertical css-qgg3xn">
		<input id="post_status" type="hidden" value="<?=$post_status?>">
		<!-- script src="https://cdn.tiny.cloud/1/shsntdzbued944a1hl3gal5e4k9a08m62jclfn94x5oboxy5/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script -->
		<script src="/ckeditor/ckeditor.js"></script>
		<script>
		function discard_changes(event)
		{
			event.preventDefault();
			window.history.back();
		}
		
		function save_post_draft(event)
		{
			event.preventDefault();
			jQuery('#post_status').val('draft');
			save_post_ajax();
		}
		
		function save_post_publish(event)
		{
			event.preventDefault();
			console.log('save_post_publish');
			console.log(save_post_publish);
			jQuery('#post_status').val('publish');
			save_post_ajax();
		}
		
		function prepare_variables()
		{
			var post_title = jQuery('#post_title').val();
		
			if(typeof tinymce !== 'undefined')
			{
				var post_content = tinymce.get('post_content').getContent();
			}
			else if(typeof CKEDITOR !== 'undefined')
			{
				var post_content = CKEDITOR.instances.post_content.getData();
			}
			else
			{
				var post_content = jQuery('#post_content').val();
			}
			
			var post_status = jQuery('#post_status').val();
			var service_ids = jQuery('#service_ids').val();
		
			var post_data = {
					post_id: <?=$waiver_id?>,
					post_title: post_title,
					post_content: post_content,
					post_status: post_status,
					post_type: 'waiver',
					service_ids: service_ids,
					security: '<?php echo wp_create_nonce('edit-post-nonce'); ?>',
				};
			
			return post_data;
		}
		
		var loading = 0;
		
		function save_post_ajax()
		{
			//button_save_publish
			
			console.log('save_post_ajax');
			console.log(save_post_ajax);
			//show_message_box('Success', 'event saved');
			//return;
			var post_data = prepare_variables();
			
			console.log('post_data');
			console.log(post_data);
			//return;
		
			jQuery.ajax({
				type: 'POST',
				dataType: 'html',
				url: '<?=get_stylesheet_directory_uri()?>/wph-save-post-ajax.php',
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
							//show_system_message('Waiver Saved.', 'Your waiver has been saved. Take next action from the below button.', 'success');
							if(response.message)
								show_system_message(
													'Waiver Saved.', 
													response.message, 
													'success', 
													'Back To Waivers',
													'/waivers/', 
													'Create an Event',
													'/create-event/'
								);
							else
								show_system_message(
													'Waiver saved.', 
													'Your waiver has been saved. Take next action from the below button.', 
													'success',
													'Back To Waivers',
													'/waivers/', 
													'Create an Event',
													'/create-event/'
								);
						}
						else
						{
							//show_system_message('Waiver hasn\'t been created', '[span]Error![/span] Your waiver has not been saved. Go back to the waiver and check if everything is filled in correctly.', 'error');
							if(response.message)
								show_system_message(
													'Waiver hasn\'t been created', 
													'[span]Error![/span] Your waiver has not been saved. Go back to the waiver and check if everything is filled in correctly.<br>[span]' + response.message + '[/span]', 
													'error',
													'Back To Waiver',
													'', 
													'Create an Event',
													'/create-event/'
								);
							else
								show_system_message(
													'Waiver hasn\'t been created', 
													'[span]Error![/span] Your waiver has not been saved. Go back to the waiver and check if everything is filled in correctly.', 
													'error',
													'Back To Waiver',
													'', 
													'Create an Event',
													'/create-event/'
								);
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
		
		jQuery(document).ready(function() {
			jQuery("#applies_to").select2({
			    placeholder: "Search apply",
			    allowClear: true
			});
		});
		</script>		
		<div class="ant-row etn-mltv-general-basic-info etn-mltv-general-block css-qgg3xn" style="row-gap: 40px;">
			<div class="event-info-row-1">
				<div class="ant-form-item css-qgg3xn">
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="age_group-lable">
									<label for="post_title" class="ant-form-item-required" title="Waiver Name">Waiver Name <span class="required_field">*</span></label>
								</div>
								<div class="age_group-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="post_title" name="post_title" type="text" placeholder="Enter Waiver Name" class="ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$post_title?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
<?php
	if($waiver_id)
	{
		$service_ids = get_post_meta($waiver_id, 'service_ids', true);
		//vd($service_ids, '$service_ids');
	}
	
?>
					<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px; display:none;">
						<div class="ant-form-item css-qgg3xn">
							<div class="ant-row ant-form-item-row css-qgg3xn">
								<div class="add_level-lable">
									<label for="applies_to" class="" title="Applies To">Applies To</label>
								</div>
								<div class="add_level-field">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<style>
											.custom-dropdown {
											  position: relative;
											  /*display: inline-block;*/
											}
											
											.dropdown-toggle {
											  border: 1px solid #ced4da;
											  padding: 5px;
											  cursor: pointer;
											  display: inline-block;
											}
											
											.dropdown-content {
											  display: none;
											  position: absolute;
											  background-color: #fff;
											  border: 1px solid #ced4da;
											  /*padding: 5px 10px;*/
											  padding: 5px;
											  z-index: 100;
											}
											
											.dropdown-content label {
											  display: block;
											  /*margin-bottom: 5px;*/
											}
											
											.dropdown-content input[type="checkbox"] {
											  margin-right: 5px;
											}
											
											.dropdown-content input[type="checkbox"]:checked + label {
											  font-weight: bold;
											}
											
											.dropdown-content.show {
											  display: block;
											}
											.dropdown-content label, .dropdown-toggle label {
												padding: 5px;
											}
											.dropdown-content label:hover {
												background: #5fbff9;
											}
											.custom-dropdown div {
												width: 100%;
											}
											.services-container {
												margin-bottom: 30px;
											}
											
											.services-container div {
												display: inline-block;
												border: 1px solid #EAEAEA;
												padding: 5px;
												/*font-size: 18px;*/
												color: #000;
												margin-right: 8px;
												min-width: 120px;
											}
											
											.services-container div, .edit--block .fields--col input[type=text], .edit--block .fields--col input[type=tel], .edit--block .fields--col input[type=email], .edit--block .fields--col input[type=url] {
											    border-radius: 4px;
											}
											.fields--col p {
												font-size: 14px;
												line-height: 1.4;
												font-weight: 400;
												color: var(--e-global-text-color);
												margin-bottom: 5px;
											}
								
											.remove-service {
												background-image: url('/wp-content/themes/the7childtheme/social/remove.png');
												width:20px;
												height:20px;
												display: inline-block;
												float: right;
												margin: 4px;
											}
											.arrow-down {
												background-image: url('/wp-content/themes/the7childtheme/social/arrow-down.png');
												width:12px;
												height:7px;
												display: inline-block;
												float: right;
												margin: 11px;
											}
											</style>
											<script>
											function register_toggle_dropdown()
											{
												var dropdownContent = document.querySelector('.dropdown-content');
												console.log('dropdownToggle click');
												dropdownContent.classList.add('show');
											}
											
											document.addEventListener('DOMContentLoaded', function() {
												var dropdownToggle = document.querySelector('.dropdown-toggle');
												var dropdownContent = document.querySelector('.dropdown-content');
												
												dropdownToggle.addEventListener('click', register_toggle_dropdown);
												
												window.addEventListener('click', function(event) {
													if (!event.target.matches('.dropdown-toggle') && !event.target.matches('.dropdown-content')) {
														var dropdownContent = document.querySelector('.dropdown-content');
														if(dropdownContent)
															dropdownContent.classList.remove('show');
														console.log('window click');
													}
												});
												dropdownContent.addEventListener('click', function(event) {
													event.stopPropagation();
												});
								
												const remove_service_buttons = document.querySelectorAll('.remove-service');
												
												remove_service_buttons.forEach(button => {
													button.addEventListener('click', function(event) {
														var service = document.getElementById('service_' + this.dataset.service);
														service.remove();
													});
												});
											});
											
											var chosen_services = [];
											
											function register_add_services()
											{
												var services_container = document.getElementById('services_container');
												console.log('services_container');
												console.log(services_container);
												
												var dropdownToggle = document.querySelector('.dropdown-toggle');
												var services = document.getElementsByClassName('checkbox-option');
												var hidden_field = document.getElementById('services');
												var service_ids = document.getElementById('service_ids');
												
												if(services)
												{
													hidden_field.value = '';
													chosen_services = [];
													chosen_service_names = [];
													dropdownToggle.innerHTML = 'Select event';
													var j = 0;
													for(var i = 0; i < services.length; i++)
													{
														if(services[i].checked)
														{
															console.log('services[' + i + '].value');
															console.log(services[i].value);
															chosen_services[j] = services[i].value;
															chosen_service_names[j] = services[i].dataset.service_name;
															j++;
														}
													}
													
													if(chosen_service_names.length)
													{
														dropdownToggle.innerHTML = chosen_service_names.join(', ');
														hidden_field.value = chosen_service_names;
														service_ids.value = chosen_services;
													}
												}
											}
											</script>
<?php
			if($waiver_id)
				$user_events = get_user_events($current_user_id, 'all');
			else
				$user_events = get_user_events($current_user_id, 'all', TRUE);
			
			$services_arr = [];
			if($service_ids)
			{
				$services_arr = explode(',', $service_ids);
			}
			//var_dump($user_events[0]);
?>
											<div class="custom-dropdown">
												<div onclick="register_toggle_dropdown()" class="dropdown-toggle">Select event<span class="arrow-down"></span></div>
												<div class="dropdown-content" onclick="register_add_services()">
<?php
			foreach($user_events as $user_event)
			{
				$post_title = sanitize_text_field($user_event->post_title);
				$checked = in_array($user_event->ID, $services_arr) ? ' checked' : '';
?>
												<label><input class="checkbox-option" type="checkbox" value="<?=$user_event->ID?>" data-service_name="<?=$post_title?>"<?=$checked?>> <?=$post_title?></label>
<?php
			}
?>
												<input type="hidden" name="services" id="services">
												<input type="hidden" name="service_ids" id="service_ids" value="<?=$service_ids?>">
												<script>
												register_add_services();
												</script>
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
			<div class="ant-col ant-col-24 css-qgg3xn">
				<div class="ant-form-item css-qgg3xn">
					<div class="ant-row ant-form-item-row css-qgg3xn">
						<div class="ant-col ant-form-item-label css-qgg3xn">
							<label for="post_content" class="ant-form-item-required" title="event description">Waiver Agreement Terms <span class="required_field">*</span></label>
						</div>
						<div class="ant-col ant-form-item-control css-qgg3xn">
							<div class="ant-form-item-control-input">
								<div class="ant-form-item-control-input-content">
									<textarea id="post_content" name="post_content"><?=$post_content?></textarea>
									<script>
									/*
									tinymce.init({
									    selector: '#post_content',
									    plugins: 'autoresize link lists',
									    toolbar: 'undo redo | formatselect | bold italic | link | bullist numlist',
									    height: 600,
									});
									*/
									CKEDITOR.replace( 'post_content', {
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
		</form>
	</div>
</div>

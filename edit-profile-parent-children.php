<style>
.sports-label, .sports-input, .current_team-label, .current_team-input, .level-label, .level-input {
	display:none;
}
</style>
<?php
$activity_types = get_all_activity_types();
?>
<style>
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
<script>
document.addEventListener("DOMContentLoaded", function() {
	init_multiselects();
});
</script>
			<div class="ant-row css-qgg3xn personal_info-row1">
				<div class="edit-post-actions">
					<span id="arrow_my_children" onclick="expand_block('my_children')" class="arrow-up"></span>
					<h4>My Players</h4>
					<div class="ant-form-item-control-input-content card-actions">
						<button type="button" id="add_player_button" value="Add Player" onclick="javascript:add_player()">Add Player</button>
					</div>
				</div>
<?php
	$genders = array(
	    'Male' => 'Male',
	    'Female' => 'Female',
	    'Other / Non-binary' => 'Other / Non-binary',
	    'Prefer not to disclose' => 'Prefer not to disclose',
	);
	
	$children = get_user_children($user_id);
	
	if($children)
	{
		$child_current = 1;
		$children_count = count($children);
		
		foreach($children as $child_id => $child_name)
		{
			$child_current++;
			$child_post = get_post($child_id);
			
			if($child_post)
			{
				$child_first_name = get_post_meta($child_id, 'first_name', TRUE);
				$child_last_name = get_post_meta($child_id, 'last_name', TRUE);
				$child_date_of_birth = get_post_meta($child_id, 'date_of_birth', TRUE);
				$child_sports = get_post_meta($child_id, 'sports', TRUE);
				$child_current_team = get_post_meta($child_id, 'current_team', TRUE);
				$child_level = get_post_meta($child_id, 'level', TRUE);
				$child_gender = get_post_meta($child_id, 'gender', TRUE);
?>
				<div id="child_container_<?=$child_id?>" data-player_name="<?=$child_first_name?> <?=$child_last_name?>">
					<div class="my_children_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="add_new_location-col">
								<div class="street-1-label">
									<label for="first_name_child_<?=$child_id?>" class="ant-form-item-required" title="First Name">First Name</label> <span class="required_field">*</span>
								</div>
								<div class="street-1-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="first_name_child_<?=$child_id?>" name="first_name_child_<?=$child_id?>" type="text" placeholder="" class="input_child_<?=$child_id?> ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$child_first_name?>">
										</div>
									</div>
								</div>
								<div class="street-2-label">
									<label for="last_name_child_<?=$child_id?>" class="ant-form-item-required" title="Last Name">Last Name</label> <span class="required_field">*</span>
								</div>
								<div class="street-2-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="last_name_child_<?=$child_id?>" name="last_name_child_<?=$child_id?>" type="text" placeholder="" class="input_child_<?=$child_id?> ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$child_last_name?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="my_children_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="add_new_location-col">
								<div class="street-1-label">
									<label for="date_of_birth_child_<?=$child_id?>" class="ant-form-item-required" title="Date of Birth">Date of Birth</label> <span class="required_field">*</span>
								</div>
								<div class="street-1-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
<?php
			generate_date_selects($child_id, $child_date_of_birth);
?>
											<input id="date_of_birth_child_<?=$child_id?>" name="date_of_birth_child_<?=$child_id?>" type="hidden" placeholder="" class="input_child_<?=$child_id?> ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$child_date_of_birth?>">
										</div>
									</div>
								</div>
								<div class="street-2-label">
									<label for="gender_child_<?=$child_id?>" class="ant-form-item-required" title="Gender">Gender</label> <span class="required_field">*</span>
									
									
								</div>
								<div class="street-2-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<select name="gender_child_<?=$child_id?>" id="gender_child_<?=$child_id?>" class="input_child_<?=$child_id?> etn_event_select2 etn_event_select etn_event_location">
												<option></option>
<?php
				foreach($genders as $gender_code => $gender_name)
				{
					$selected = $child_gender == $gender_code ? ' selected' : '';
?>
											<option value="<?=$gender_code?>"<?=$selected?>><?=$gender_name?></option>
<?php
				}
?>
											</select>


											<!-- input id="sports_child_<?=$child_id?>" name="sports_child_<?=$child_id?>" type="text" placeholder="" class="input_child_<?=$child_id?> ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$child_sports?>" -->
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="my_children_subblock ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="add_new_location-col">
								<div class="street-1-label current_team-label">
									<label for="current_team_child_<?=$child_id?>" class="ant-form-item-required" title="Current Team">Current Team</label>
								</div>
								<div class="street-1-input current_team-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="current_team_child_<?=$child_id?>" name="current_team_child_<?=$child_id?>" type="text" placeholder="" class="input_child_<?=$child_id?> ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$child_current_team?>">
										</div>
									</div>
								</div>
								<div class="street-2-label level-label">
									<label for="level_child_<?=$child_id?>" class="ant-form-item-required" title="Level">Level</label>
								</div>
								<div class="street-2-input level-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<input id="level_child_<?=$child_id?>" name="level_child_<?=$child_id?>" type="text" placeholder="" class="input_child_<?=$child_id?> ant-input css-qgg3xn create_event_input create_event_input_text" value="<?=$child_level?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="my_children_subblock<?php if($child_current <= $children_count) echo ' last_block'; else  echo ' last_block_no_border'; ?> ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
						<div class="ant-form-item css-qgg3xn">
							<div class="add_new_location-col">
								<div class="street-1-label sports-label">
								
									<label for="sports_child_<?=$child_id?>" class="ant-form-item-required" title="Sports">Sports/ Interests</label> <span class="required_field">*</span>
								
								</div>
								<div class="street-1-input sports-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">

<?php
			$selected_values = explode(',', $child_sports);
			render_activity_multiselect($activity_types, $child_id, $selected_values);
?>



										</div>
									</div>
								</div>
								<div class="street-1-input">
									<div class="ant-form-item-control-input">
										<div class="ant-form-item-control-input-content">
											<button type="button" onclick="javascript:delete_child(<?=$child_id?>)">Delete</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<input id="post_id_child_<?=$child_id?>" name="post_id_child_<?=$child_id?>" type="hidden" value="<?=$child_id?>" class="input_child_<?=$child_id?> child_id">
					<input id="delete_child_<?=$child_id?>" name="delete_child_<?=$child_id?>" type="hidden" value="0" class="input_child_<?=$child_id?>">
				</div>
<?php
			}
		}
	}
?>

				<div id="new_player" class="my_children_subblock new_player ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px; display:none;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="first_name_child_0" class="ant-form-item-required" title="First Name">First Name</label>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="first_name_child_0" name="first_name_child_0" type="text" placeholder="" class="input_child_0 ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
							<div class="street-2-label">
								<label for="last_name_child_0" class="ant-form-item-required" title="Last Name">Last Name</label>
							</div>
							<div class="street-2-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="last_name_child_0" name="last_name_child_0" type="text" placeholder="" class="input_child_0 ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="my_children_subblock new_player ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label">
								<label for="first_name_child_0" class="ant-form-item-required" title="Date of Birth">Date of Birth</label>
							</div>
							<div class="street-1-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
<?php
			generate_date_selects(0, '');
?>
										<input id="date_of_birth_child_0" name="date_of_birth_child_0" type="hidden" placeholder="" class="input_child_0 ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
							<div class="street-2-label">

								<label for="gender_child_0" class="ant-form-item-required" title="Gender">Gender</label>

							</div>
							<div class="street-2-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<select name="gender_child_0" id="gender_child_0" class="input_child_0 etn_event_select2 etn_event_select etn_event_location">
											<option></option>
<?php
				foreach($genders as $gender_code => $gender_name)
				{
?>
											<option value="<?=$gender_code?>"><?=$gender_name?></option>
<?php
				}
?>
										</select>
										<!-- input id="sports_child_0" name="sports_child_0" type="text" placeholder="" class="input_child_0 ant-input css-qgg3xn create_event_input create_event_input_text" value="" -->
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="my_children_subblock new_player ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label current_team-label">
								<label for="current_team_child_0" class="ant-form-item-required" title="Current Team">Current Team</label>
							</div>
							<div class="street-1-input current_team-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="current_team_child_0" name="current_team_child_0" type="text" placeholder="" class="input_child_0 ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
							<div class="street-2-label level-label">
								<label for="level_child_0" class="ant-form-item-required" title="Level">Level</label>
							</div>
							<div class="street-2-input level-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
										<input id="level_child_0" name="level_child_0" type="text" placeholder="" class="input_child_0 ant-input css-qgg3xn create_event_input create_event_input_text" value="">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="my_children_subblock new_player ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<div class="ant-form-item css-qgg3xn">
						<div class="add_new_location-col">
							<div class="street-1-label sports-label">

								<label for="sports_child_0" class="ant-form-item-required" title="Sports">Sports</label>

							</div>
							<div class="street-1-input sports-input">
								<div class="ant-form-item-control-input">
									<div class="ant-form-item-control-input-content">
									
									
<?php
			$selected_values = [];
			render_activity_multiselect($activity_types, 0, $selected_values);
?>
									
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<input id="post_id_child_0" name="post_id_child_0" type="hidden" value="0" class="input_child_0 child_id">
				<div class="ant-col ant-col-xs-24 ant-col-sm-24 ant-col-md-12 css-qgg3xn" style="padding-left: 8px; padding-right: 8px;">
					<p style="font-style: italic;">This information will be used to inform you of openings for different events and sports in the future.</p>
				</div>
			</div>

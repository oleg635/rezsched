<script>
function show_loader()
{
	var loader_overlay = document.getElementById('loader-overlay');
	if(loader_overlay)
		loader_overlay.style.display = 'flex';
}

function hide_loader()
{
	var loader_overlay = document.getElementById('loader-overlay');
	if(loader_overlay)
		loader_overlay.style.display = 'none';
}
</script>
<style>
#loader-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(255, 255, 255, 0.7);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.loader-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #ccc;
  border-top: 4px solid #333;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
<div id="loader-overlay" style="display:none;">
  <div class="loader-spinner"></div>
</div>
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

.ff_add--social-media .close--system_message {
    display: inline-block;
    position: absolute;
    right: 16px;
    top: 16px;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.waiting {
    position: relative;
    display: inline-block;
}

.waiting::before {
    content: "";
    position: absolute;
	top: 25px;
	left: 14px;
	width: 10px;
	height: 10px;
	margin-left: -10px; /* Center the spinner horizontally */
    margin-top: -10px;  /* Center the spinner vertically */
    border: 3px solid rgba(0, 0, 0, 0.3);
    border-radius: 50%;
    border-top-color: #000;  /* Spinner color */
    animation: spin 1s ease infinite; /* Rotating animation */
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
<script>
function waiting(mode, button_id)
{
	if(mode)
	{
		show_system_message(
							'Wait please', 
							'Processing...', 
							'success', 
							'',
							'', 
							'', 
							''
		);
		
		if(button_id)
		{
			var charge_button = document.getElementById(button_id);
			if(charge_button)
				charge_button.classList.add('waiting');
		}
	}
	else
	{
		close_system_message('');
		
		if(button_id)
		{
			var charge_button = document.getElementById(button_id);
			if(charge_button)
				charge_button.classList.remove('waiting');
		}
	}
}

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

var open_new_window = false;

function close_system_message(redirect)
{
<?php
	//if($_SERVER['REMOTE_ADDR'] == '37.120.156.24' || $_SERVER['REMOTE_ADDR'] == '92.60.179.128')
	if(0)
	{
?>
	alert(open_new_window);
<?php
	}
?>
	var schedule_popup = document.getElementById('system_message');
	schedule_popup.style.display = 'none';
	//alert(redirect);
	if(redirect !== undefined && redirect !== '')
	{
		open_new_window = 0;
		if(typeof open_new_window !== 'undefined' && open_new_window)
		{
			open_new_window = false;
			window.open(redirect, '_blank');
		}
		else
			window.location.href = redirect;
		return false;
	}
}

function update_system_message(message, append)
{
	var system_message_text = window.parent.document.getElementById('system_message_text');
	if(system_message_text)
		if(append)
			system_message_text.innerHTML += message;
		else
			system_message_text.innerHTML = message;
}

function show_system_message(title, text, class_name, button1_title, redirect1, button2_title, redirect2, new_window)
{
	hide_loader();
	console.log('show_system_message! ' + button1_title);

	if(typeof new_window !== 'undefined' && new_window)
		open_new_window = new_window;
	else
		open_new_window = false;

	document.getElementById('system_message_title').innerHTML = title;
	
	var txt = text.replaceAll('[', '<');
	txt = txt.replaceAll(']', '>');
	
	document.getElementById('system_message_text').innerHTML = txt;
	
	var social_media = document.getElementById('system_message');
	social_media.className = 'ff_add--social-media';
	social_media.classList.add('system_message_' + class_name);
	/*	
	alert(
			''
			+ button1_title + "\r\n"
			+ redirect1 + "\r\n"
			+ button2_title + "\r\n"
			+ redirect2 + "\r\n"
	);
	*/
	if(typeof button1_title !== 'undefined' && button1_title !== '')
	{
		document.getElementById('system_message_button1_title').innerHTML = button1_title;
		document.getElementById('system_message_button1_title').parentNode.style.display = 'initial';
	}
	else
	{
		document.getElementById('system_message_button1_title').parentNode.style.display = 'none';
	}
	
	console.log('button1_title');
	console.log(button1_title);
	
	if(typeof redirect1 !== 'undefined')
	{
		document.getElementById('system_message_button1').addEventListener('click', function() {
			close_system_message(redirect1);
		});
	}
	
	if(typeof button2_title !== 'undefined' && button2_title !== '')
	{
		document.getElementById('system_message_button2_title').innerHTML = button2_title;
		document.getElementById('system_message_button2_title').parentNode.style.display = 'initial';
	}
	else
	{
		document.getElementById('system_message_button2_title').parentNode.style.display = 'none';
	}
	
	if(typeof redirect2 !== 'undefined')
	{
		document.getElementById('system_message_button2').addEventListener('click', function() {
			close_system_message(redirect2);
		});
	}
	
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
<div class="ant-modal-mask" id="etn_multivendor_form" style="position: relative;">
	<a href="javascript:void(0)" class="close--system_message" onclick="javascript:close_system_message('')">
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
						<div class="ant-modal-confirm-body ant-modal-confirm-body-has-title"><span role="img" aria-label="check-circle" class="anticon anticon-check-circle" style="font-size: 50px;"><svg viewBox="64 64 896 896" focusable="false" data-icon="check-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm0 820c-205.4 0-372-166.6-372-372s166.6-372 372-372 372 166.6 372 372-166.6 372-372 372z" fill="#5d5dff"></path><path d="M512 140c-205.4 0-372 166.6-372 372s166.6 372 372 372 372-166.6 372-372-166.6-372-372-372zm193.4 225.7l-210.6 292a31.8 31.8 0 01-51.7 0L318.5 484.9c-3.8-5.3 0-12.7 6.5-12.7h46.9c10.3 0 19.9 5 25.9 13.3l71.2 98.8 157.2-218c6-8.4 15.7-13.3 25.9-13.3H699c6.5 0 10.3 7.4 6.4 12.7z" fill="#f0f2ff"></path><path d="M699 353h-46.9c-10.2 0-19.9 4.9-25.9 13.3L469 584.3l-71.2-98.8c-6-8.3-15.6-13.3-25.9-13.3H325c-6.5 0-10.3 7.4-6.5 12.7l124.6 172.8a31.8 31.8 0 0051.7 0l210.6-292c3.9-5.3.1-12.7-6.4-12.7z" fill="#5d5dff"></path></svg></span>
							<div class="ant-modal-confirm-paragraph"><span id="system_message_title" class="ant-modal-confirm-title">Event Created.</span>
								<div id="system_message_text" class="ant-modal-confirm-content">Your event has been created. Take next action from the below button.</div>
							</div>
						</div>
						<div class="ant-modal-confirm-btns">
							<button id="system_message_button1" type="button" class="ant-btn css-qgg3xn ant-btn-default"><span id="system_message_button1_title">Back to Events</span></button>
							<button id="system_message_button2" type="button" class="ant-btn css-qgg3xn ant-btn-primary"><span id="system_message_button2_title">Create another</span></button>
						</div>
					</div>
				</div>
			</div>
			<div tabindex="0" aria-hidden="true" style="width: 0px; height: 0px; overflow: hidden; outline: none;"></div>
		</div>
	</div>
</div>
</section>

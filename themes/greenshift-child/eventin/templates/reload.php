<?php
if(!empty($_REQUEST['redirect']))
{
	header('location:' . $_REQUEST['redirect'] . '?booked=1');
	return;
}
?>
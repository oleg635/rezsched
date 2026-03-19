<?php
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$user_id = get_query_var('user_id');

if($user_id === '0')
{
?>
<p>Please save the user first.</p>
<?php
}
else
{
	if($user_id === '')
		$user_id = get_current_user_id();
	else
		$user_id = intval($user_id);
	
	$nonce = wp_create_nonce('wp_rest');
?>
<script src="https://js.stripe.com/v3/"></script>
<form id="payment-form">
    <div>
        <label for="card-name">Card Name</label>
        <input type="text" id="card-name" name="card-name" required>
    </div>
    <div>
        <label for="card-element">Credit or Debit Card</label>
        <div id="card-element"></div>
        <div id="card-errors" role="alert"></div>
    </div>
    <button type="submit">Add Payment Method</button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var stripe = Stripe('<?=Stripe_Publishable_Key?>');

        var elements = stripe.elements();
        var card = elements.create('card');
        card.mount('#card-element');

        card.on('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // Create a card token using createToken instead of createPaymentMethod
            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    // Send the card token (tok_XXXXXXX) to the backend
                    fetch('/wp-json/custom/v1/save-payment-method', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo esc_js($nonce); ?>'
                        },
                        body: JSON.stringify({
                            user_id: <?=$user_id?>,
                            token_id: result.token.id,
                        }),
                    }).then(function(response) {
                        return response.json();
                    }).then(function(data) {
                        if (data.success) {
<?php
	if(!empty($_REQUEST['redirect']))
	{
?>
                            window.location.href = '<?php echo $_REQUEST['redirect']; ?>';
<?php
	}
	else
	{
?>
                            window.location.href = '/edit-profile/<?php if($user_id !== get_current_user_id()) echo $user_id; ?>';
<?php
	}
?>
                        } else {
                            alert('Failed to add payment method.');
                        }
                    });
                }
            });
        });
    });
</script>
<?php
}
?>
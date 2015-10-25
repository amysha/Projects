<h2>Make a Payment</h2>

<style>
	input { display: block;}
</style>

<?php 
	echo "<p>" . anchor('store/showShoppingCart','Go Back to Shopping Cart') . "</p >";
	
	echo form_open_multipart("store/collectPayment");
	if (isset($errorMsg)){
		echo '<p style="color:red">' . $errorMsg . "</p >";
	}
	
	echo "<p> Total:  $" . number_format((float)$total, 2) . "</p>";
	
	echo form_label('Credit Card Number'); 
	echo form_error('creditcard_number');
	echo form_input('creditcard_number', '',"required");

	echo form_label('Expiry Month (MM)'); 
	echo form_error('creditcard_month');
	echo form_input('creditcard_month', '',"required");

	echo form_label('Expiry Year (YY)'); 
	echo form_error('creditcard_year');
	echo form_input('creditcard_year', '',"required");

	echo form_submit('submit', 'Pay Now');
	echo form_close();	
?>
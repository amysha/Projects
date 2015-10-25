<center>
<h3>Order Reciept</h3>

<?php 
	echo "Order ID: $order_id <br>";

	echo "<table border=0 cellspacing=2 cellpadding=2>";
	echo "<tr><th>Name</th><th>Quantity</th><th>Price (ea)</th><th>Item Subtotal</th></tr>";

	for ($i = 0; $i<count($product_name); $i++) {
		echo "<tr>";
			echo "<td tyle='text-align:left'>".$product_name[$i]."</td>";
			echo "<td tyle='text-align:left'>".$quantity[$i]."</td>";
			echo "<td style='text-align:left'>".number_format((float)$price[$i], 2)."</td>";
			echo "<td style='text-align:left'>".number_format((float)$quantity[$i]*$price[$i], 2)."</td>";
		echo "</tr>";
	}
	echo "<tr><th>Total</th><th>".number_format((float)$total, 2)."</th><tr>";
	echo "</table>";

?>
</center>
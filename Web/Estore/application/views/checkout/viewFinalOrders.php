<h2>Finalized Orders</h2>
<?php 
	echo "<p>" . anchor('store/logout','Logout') . "</p>";
	echo "<p>" . anchor('store/index','Back') . "</p>";
	echo "<td>" . anchor("store/deleteAll",'Delete All') . "</td>";
	
	echo "<table>";
	echo "<tr><th>ID</th><th>Customer ID</th><th>Order Date</th><th>Order Time</th><th>Total Amount</th><th>Creditcard Number</th><th>Expiry Month</th><th>Expiry Year</th></tr>";
		
	foreach ($orders as $order) {
		echo "<tr>";
		echo "<td>" . $order->id . "</td>";
		echo "<td>" . $order->customer_id . "</td>";
		echo "<td>" . $order->order_date . "</td>";
		echo "<td>" . $order->order_time . "</td>";
		echo "<td>" . $order->total . "</td>";
		echo "<td>" . $order->creditcard_number . "</td>";
		echo "<td>" . $order->creditcard_month . "</td>";
		echo "<td>" . $order->creditcard_year . "</td>";

		echo "</tr>";
	}
	echo "<table>";
	
?>
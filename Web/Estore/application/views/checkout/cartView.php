<h2>Shopping Cart</h2>
<?php 
	echo "<p>" . anchor('store/index','Back') . "</p>";
	echo "<p>" . anchor('store/logout','Logout') . "</p>";	
		
	if(!$order_items)
	{
		echo "<p>Your cart is empty :( Please buy something!<p>";
		return;
	}
	
	echo "<table>";
	echo "<tr><th>Name</th><th>Description</th><th>Price</th><th>Photo</th><th>Quantity</th></tr>";
	
	$i = 0;
	foreach ($product_list as $product) {
		echo "<tr>";
		echo "<td>" . $product->name . "</td>";
		echo "<td>" . $product->description . "</td>";
		echo "<td>" . $product->price . "</td>";
		echo "<td><img src='" . base_url() . "images/product/" . $product->photo_url . "' width='100px' /></td>";
		echo "<td>";
		echo form_open("store/updateQuantity/$i");
		echo form_input('quantity',$order_items[$i]->quantity,'required');
		echo form_submit('submit', 'Update');
		echo form_close();
		echo "</td>";
		echo "<td>" . anchor("store/removeFromCart/$i",'Remove',
				"onClick='return confirm(\"Do you really want to remove this item from the cart?\");'") . "</td>";
			
		echo "</tr>";
		$i += 1;
	}
	echo "<table>";
		
	echo "<p> Total:  $" . number_format((float)$total, 2) . "</p>";
	echo "<p>" . anchor('store/collectPayment','Checkout') . "</p>";
?>	


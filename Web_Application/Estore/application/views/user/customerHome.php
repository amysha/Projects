<h2>Welcome back!</h2>
<?php 
	echo "<p>" . anchor('store/login','Logout') . "</p>";
	echo "<p>" . anchor('store/showShoppingCart','Shopping Cart') . "</p>";
	
	
	echo "<table>";
	echo "<tr><th>Name</th><th>Description</th><th>Price</th><th>Photo</th></tr>";
		
	foreach ($products as $product) {
		echo "<tr>";
		echo "<td>" . $product->name . "</td>";
		echo "<td>" . $product->description . "</td>";
		echo "<td>" . $product->price . "</td>";
		echo "<td><img src='" . base_url() . "images/product/" . $product->photo_url . "' width='100px' /></td>";

		echo "<td>" . anchor("store/read/$product->id",'View Details') . "</td>";
		echo "<td>" . anchor("store/addToCart/$product->id",'Add to Cart') . "</td>";
		
		echo "</tr>";
	}
	echo "<table>";
	
?>
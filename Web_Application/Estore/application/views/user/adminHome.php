<h2>Admin Home</h2>
<?php 
		echo "<p>" . anchor('store/login','Logout') . "</p>";
		echo "<p>" . anchor('store/newForm','Add New Product') . "</p>";
		echo "<p>" . anchor('store/viewFinalOrders','View Finalized Orders') . "</p>";
		echo "<p>" . anchor('store/deleteAll','Delete all customers and order information') . "</p>";
				
		echo '<table style="border:1px solid black;">'; 
		echo '<tr>
				<th style="border:1px solid black;">Name</th>
				<th style="border:1px solid black;">Description</th>
				<th style="border:1px solid black;">Price</th>
				<th style="border:1px solid black;">Photo</th></tr>';
		
		foreach ($products as $product) {
			echo "<tr>";
			echo '<td style="border:1px solid black;">' . $product->name . "</td>";
			echo '<td style="border:1px solid black;">' . $product->description . "</td>";
			echo '<td style="border:1px solid black;">' . $product->price . "</td>";
			echo "<td><img src='" . base_url() . "images/product/" . $product->photo_url . "' width='100px' /></td>";
				
			echo "<td>" . anchor("store/delete/$product->id",'Delete',"onClick='return confirm(\"Do you really want to delete this record?\");'") . "</td>";
			echo "<td>" . anchor("store/editForm/$product->id",'Edit') . "</td>";
			echo "<td>" . anchor("store/read/$product->id",'View') . "</td>";
				
			echo "</tr>";
		}
		echo "<table>";
?>	
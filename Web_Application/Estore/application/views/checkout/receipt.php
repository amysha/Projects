<h2>Order Complete</h2>
 
<?php 
    echo "<p>" . anchor('store/index','Go back to Store') . "</p>";
    echo "<p>" . anchor('store/login','Logout') . "</p>";
    echo "<p> Thank you for shopping at the Baseball Card Kingdom, a copy of your reciept has been emailed to you </p>";
    echo "Order ID: $order_id <br>";
    
    echo "<table>";
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
 
<form>
    <input type=button value="Print Reciept" onClick="receipt()">
</form>
 
<script language="JavaScript">
	
	var name = <?php echo json_encode($product_name); ?>;
	var name_list = name.split(',');
    var quantity = <?php echo json_encode($quantity); ?>;
    var price = <?php echo json_encode($price); ?>;
    var order_id = <?php echo json_encode($order_id); ?>;
    var total = <?php echo json_encode($total); ?>;
 
    function receipt() {
     top.wRef=window.open('','myconsole',
      'width=500,height=450,left=10,top=10'
       +',menubar=1'
       +',toolbar=0'
       +',status=1'
       +',scrollbars=1'
       +',resizable=1');
     top.wRef.document.writeln(
      '<html><head><title>Order Reciept</title></head>'
     +'<body bgcolor=white onLoad="self.focus()">'
     +'<center><font color=red><b><i>For printing, <a href=# onclick="window.print();return false;">click here</a> or press Ctrl+P</i></b></font>'
     +'<H3>Order Reciept</H3>'
     +'<table>'
     )
 
    buf='';
 
    buf+='Order Id: ' + order_id + '<br>';
    buf+='Total: ' + parseFloat(total).toFixed(2) + '<br>';
 
    buf+='<tr><th>Name</th><th>Quantity</th><th>Price (ea)</th><th>Item Subtotal</th></tr>';

    var i = 0;
    for (i; i<quantity.length; i++) {
        console.log(name);
    	buf+= '<tr><td>'+name_list[i]+'</td><td>'+quantity[i]+"</td><td style='text-align:left'>"+parseFloat(price[i]).toFixed(2)+'</td>';
        buf+= "<td style='text-align:left'>"+parseFloat(price[i]*quantity[i]).toFixed(2)+'</td> </tr>';
    };

    buf += '<tr><th>Total</th><th>'+parseFloat(total).toFixed(2)+'</th><tr>';
    buf += '</table></center></body></html>';
    top.wRef.document.writeln(buf);
    top.wRef.document.close();
}
</script>
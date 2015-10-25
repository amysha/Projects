<!DOCTYPE html>

<html>

<head>
  <meta charset="utf-8" />
  <title><?php echo "Baseball Card Store"; ?></title>
</head>

<h1>Welcome To Baseball Card Kingdom, Stranger!</h1>
<h2>Please log in or sign up!</h2>

<style>
	input { display: block;}	
</style>

<?php 
	echo form_open_multipart('store/login');
	echo '<p style="color:red">' . $errorMsg . "</p >";
	
	echo form_label('Username'); 
	echo form_error('username');
	echo form_input('username', set_value('username'), "required");

	echo form_label('Password');
	echo form_error('password');
	echo form_password('password', '', "required");
	
	echo form_submit('submit', 'Login');
	echo form_close();
	echo "<p>" . anchor('store/signup','Sign Up') . "</p>";
?>	
</html>
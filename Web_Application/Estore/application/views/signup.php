<head>
 	<meta charset="utf-8" />
 	<title><?php echo "Baseball Card Store"; ?></title>
 	<style>
		input { display: block; }
	</style>
</head>
<h2>Sign Up</h2>
<?php 
	echo "<p>" . anchor('store/index','Back') . "</p>";
	echo form_open('store/signup');
	
	echo form_label('First Name'); 
	echo form_error('first');
	echo form_input('first', set_value('first'), "required");

	echo form_label('Last Name'); 
	echo form_error('last');
	echo form_input('last', set_value('last'), "required");

	echo form_label('User Name');
	echo form_error('username');
	echo form_input('username', set_value('username'), "required");
	
	echo form_label('Password');
	echo form_error('password');
	echo form_password('password', '', "id='pass1' required");
	
	echo form_label('Password Confirmation');
	echo form_error('passconf');
	echo form_password('passconf','', "required");
		
	echo form_label('Email');
	echo form_error('email');
	echo form_input('email', set_value('email'), "required");
	
	echo form_submit('submit', 'Submit');
	echo form_close();
?>	

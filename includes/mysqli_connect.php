<?php
	// This script connects to and selects the database
	
	// Connect:
	$dbc = mysqli_connect('ucfsh.ucfilespace.uc.edu', 'group_group1', 'cler4cap1', "group_group1", "3306");
	
	// Select:
	mysqli_select_db($dbc, 'group_group1');
?>
<?php
	define('TITLE', 'UCC CRMS');
	include('templates/header.html');
	logout();
    location.reload(true);
?>
	<h3 style="color:red">You have been successfully logged out</h3>
	<p>Please close your browser or log in below</p>
	
	<?php
	login_form();
	?>

		<!-- END CHANGEABLE CONTENT. -->
<?php

	include('templates/footer.html');
		
?>

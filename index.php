	<?php
        include('templates/header.html');

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			// connect and select server
			include('includes/mysqli_connect.php');

			// assign email and password to variables
			$email = $_POST['email'];
			$pass = $_POST['password'];
			
			// Check email address for being valid
			if(!filter_var($email, FILTER_VALIDATE_EMAIL) && !filter_var($email, FILTER_SANITIZE_EMAIL))
			{
				print "<script type='text/javascript'>alert('E-mail is not valid')</script>";
			}
			else {
				// Sanitize the email for security
				$email = filter_var($email, FILTER_SANITIZE_EMAIL);
				
				// Query for user
				$userQuery = userName($email, $pass);
					
				// if user not found, display error message
				if($user = mysqli_query($dbc, $userQuery)) {
					if(mysqli_num_rows($user) == 0) {
						define('TITLE', 'UCC CRMS');

						print "<div align='center'>";
                        print "<h4 style='color: red'>There was an error with your Email/Password combination,
                            please try again.</h4>";
						print "<p style='color: red; font-size: smaller;'>If the problem persists, please contact
							the <a href='mailto: alexanf@mail.uc.edu?subject=UCC CRMS Help' style='text-decoration: underline;'>
							system administrator</a>.
						</p>";
						print "</div>";
						// Print the login form
						login_form();
					}
					// Else if user found, login
					else {
						define('TITLE', 'UCC CRMS Dashboard');

						// Pull user database info and assign to variables
						$row = mysqli_fetch_array($user);
						$userID = $row['UserID'];
						$userTitle = $row['Title'];
						$userFirstName = $row['FirstName'];
						$userLastName = $row['LastName'];
						$userFullName = "{$row['Title']} {$row['LastName']}";
						$userAuth = $row['Active'];
                        $admin = $row['Admin'];
					
						// Complete login by setting Session variables and cookie
							// Set cookie to show user logged into site for 24 hours
							setcookie('Samuel', 'Clemens', time() + 3600*24);
							
							// Set session data to use throughout the site
							$_SESSION["userFullName"] = $userFullName;
							$_SESSION["userID"] = $userID;
							$_SESSION["userAuth"] = $userAuth;
                            $_SESSION["admin"] = $admin;
						
						/******  Begin building dashboard  *****/
						
						
						// Pass info to build the dashboard
						//dashboard($userID, $userFullName);
                        echo "<script>window.location.href='" . $_SERVER['PHP_SELF'] . "?'</script>";
					}
				}
				// Could not query the database
				else {
                    include('templates/header.html');

					print '<p class="error">Could not retrieve the data because:<br />'
						. mysqli_error($dbc) . '.</p>
						<p>The query being run was: ' . $userQuery . '</p>';
				}
			}
		}
		
		// Logs user out and reprints the login form
		else if($_SERVER['REQUEST_METHOD'] == 'GET') {
			// Check if a user is logged in
			// If so get the session variables and build the dashboard
			if(isset($_SESSION["userID"])) {
                define('TITLE', 'UCC CRMS Dashboard');
				$userFullName = $_SESSION["userFullName"];
				$userID = $_SESSION["userID"];

                dashboard($userID, $userFullName);
			}
			else {
				define('TITLE', 'UCC CRMS');
				login_form();
			}
		}
		
	?>
		<!-- END CHANGEABLE CONTENT. -->
<?php

	include('templates/footer.html');
		
?>
		
		


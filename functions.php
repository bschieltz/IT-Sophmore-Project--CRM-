<?php
	// Block php errors from showing on site
	//error_reporting(0);
	
	/****************************************************************************************/

	// Function to build and display the Login Form
	function login_form() {
		print '<h3>Please Login</h3>';

		print '
			<form id="login" action="index.php" method="post">
				<table id="loginTable">
					<th colspan="2"></th>
					<tr>
					<td><label>Email: </label></td>
					<td><input type="email" name="email" autofocus required /></td>
					</tr><tr>
					<td><label>Password: </label></td>
					<td><input type="password" name="password" required /></td>
					</tr><tr>
					<td colspan="2" style="text-align:right;"><input type="submit" value="Submit" class="myButton" /></td>
					</tr>
				</table>
			</form>
		';
	}
	
	/****************************************************************************************/

	// Search for user in the database 
	function userName($email, $pass) {
		// Search for user in the database 
		$query = "Select *
			FROM tuser join ttitle
				on tuser.titleID = ttitle.titleID
			WHERE email = '{$email}'
			AND password = '{$pass}'";
		
		return $query;
	}

	/****************************************************************************************/
		
	function pullUserNotes($userID) {
		$userID = $userID;
		
		// Query to pull all contacts
		$userNotesQuery = "SELECT * 
			FROM tuser
				JOIN tnote 
					ON tuser.userID = tnote.userID
				JOIN tinteractiontype 
					on tnote.interactiontypeID = tinteractiontype.interactiontypeID
			WHERE tuser.userID = $userID
			Order By tnote.DateTime desc";	

		return $userNotesQuery;
	}

    /****************************************************************************************/
    // Query to Pull Business

    function pullBusiness($businessID){
        include('includes/mysqli_connect.php');
        $businessQuery = "SELECT `BusinessID`, `BusinessName`, `PrimaryContact`, `PrimaryPhone#`, `Notes`
                  FROM `tbusiness` WHERE 'BusinessName = $businessID'";

        return mysqli_query($dbc, $businessQuery);

    }
	/****************************************************************************************/
	// Build Dashboard
	function dashboard($userID, $userFullName) {
		include('includes/mysqli_connect.php');
		
		if($_SESSION["userAuth"] != "1") {
			noAuth();
		}
		
		print "<h2 style='color: #E00122;'>Welcome, $userFullName!</h2>";
		
		print "<br /><form action='notes.php' method='get'><input type='submit' value='Add new contact'  class='myButton'/></form><br />";
		
		print "<br />";
		
		print "<h3>Current Action Items:</h3>";
		
		/*********** Action Items still need to be developed *********************/
		print "<ul><li>Action Items still need to be developed!</li></ul><br />";
		
		print "<h3>Recent Contacts:</h3>";
		// Pull 
		$userNotesQuery = pullUserNotes($userID);
		
		if($userNotes = mysqli_query($dbc, $userNotesQuery)) {
			if(mysqli_num_rows($userNotes) == 0) {
				print '<p style="color:red">You do not have any notes stored in the system.</p>';
			}
			else {

				$numberOfNotes = mysqli_num_rows($userNotes);
				
				print "<table id='notesTable'>";
				for($i=1; $i<=$numberOfNotes && $i <= 5; $i++) {
					if($row = mysqli_fetch_array($userNotes)) {
						$datetime = strtotime($row['DateTime']);
						$datetime = date("m-d-Y h:i a", $datetime);
						
						print "<tr>
							<td style='color: #E00122'><b>Note $i</b></td>
							<td><b>Date:</b> " . $datetime . "</td>
							<td><b>Interaction:</b> " . $row['InteractionType'] . "</td>
							</tr>
							<tr>
							<td colspan='3'><b>Notes:</b><br /> " . $row['Note'] . "</td>
							</tr>
							<tr><td colspan='3'>&nbsp</td></tr>
						";
					}
				}
				print "<tr><td colspan='3' style='text-align: center;'>See all contacts.</td></tr>";
				print "</table>";
				
			}
		}
		else {
			print "<h3>ERROR!</h3>";
		}
	}
	
	/****************************************************************************************/

	function logout() {
		// Destroy cookie
		setcookie('Samuel', 'Clemens', time() - 3600);
		// Destroy session
		session_destroy();
	}
	
	/****************************************************************************************/

	// Handle unauthorized user
	function noAuth() {
		$nogo = "Unauthorized user! Please contact your system administrator for assistance.";
		print "<SCRIPT LANGUAGE='JavaScript'>
    window.alert('$nogo')
    window.location.href='logout.php';
    </SCRIPT>";
		logout();
	}

?>
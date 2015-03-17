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
	// Pulling Notes By User for use on the Dashboard
	function pullUserNotes($userID) {
		$userID = $userID;
		
		// Query to pull all contacts
		$userNotesQuery = "SELECT tuser.UserID, InteractionType, Note, tbusiness.BusinessID as 'BusinessID',
            BusinessName, temployee.employeeID as 'employeeID', temployee.FirstName as 'FirstName',
            temployee.LastName as 'LastName', temployee.PhoneNumber as 'Phone', temployee.Extension as 'Ext',
            temployee.Email as 'Email', personalNote, DateTime
			FROM tuser
				Right JOIN tnote
					ON tuser.userID = tnote.userID
				Right JOIN tinteractiontype
					ON tnote.interactiontypeID = tinteractiontype.interactiontypeID
                Right JOIN tbusiness
                    ON tnote.businessID = tbusiness.businessID
                Right JOIN temployee
                    ON tnote.employeeID = temployee.employeeID
			WHERE tuser.userID = $userID
			Order By tnote.DateTime desc";	

		return $userNotesQuery;
	}

    /****************************************************************************************/
    // Pulling Action Items By User for use on the Dashboard
    function pullUserActionItems($userID) {
        $userID = $userID;

        // Query to pull all uncompleted action items
        $userActionItemsQuery = "
            SELECT tuser.UserID, InteractionType, Note, tbusiness.BusinessID as 'BusinessID',
                BusinessName, temployee.employeeID as 'employeeID', temployee.FirstName as 'FirstName',
                temployee.LastName as 'LastName', temployee.PhoneNumber as 'Phone', temployee.Extension as 'Ext',
                temployee.Email as 'Email', personalNote as 'EmployeeNote', tnote.DateTime as 'NoteCreated', tactionitem.DateTime as 'ActionItemCreated',
                ActionItemID, OriginalActionItemID, ReferanceID, AssignedToUserID, tactionitem.NoteID, actionComplete
            FROM tuser
                JOIN tactionitem
                    ON tuser.userID = tactionitem.AssignedToUserID
                JOIN tnote
                    ON tactionitem.noteID = tnote.noteID
                JOIN tbusiness
                    ON tnote.businessID = tbusiness.businessID
                JOIN temployee
                    ON tbusiness.businessID = temployee.businessID
                JOIN tinteractiontype
					ON tnote.interactiontypeID = tinteractiontype.interactiontypeID
            Where AssignedToUserID = $userID
                AND actionComplete is NULL
            Order By 'ActionItemCreated' desc;
            ";

        return $userActionItemsQuery;
    }

    /****************************************************************************************/
    // pullTitleList

    function pullTitles() {
        include('includes/mysqli_connect.php');
        $titleList = [];
        $titleQuery = "SELECT Title, TitleID FROM ttitle";
        if ($titleResult = mysqli_query($dbc, $titleQuery)) {
            for ($i=0; $i < mysqli_num_rows($titleResult); $i++) {
                if($row = mysqli_fetch_array($titleResult)) {
                    array_push($titleList, array($row['Title'],$row['TitleID']));
                }
            }
        }
        return $titleList;
    }

    /****************************************************************************************/
    // Display Business List

    function displayBusinessList()
    {
        include('includes/mysqli_connect.php');
        $searchString = $_GET['Search'];
        $businessListQuery = "SELECT BusinessID, BusinessName
                  FROM tbusiness
                  WHERE BusinessName like '%$searchString%'";

        $businessList = mysqli_query($dbc, $businessListQuery) or die("Error: ".mysqli_error($dbc));
        for ($i=0; $i <= mysqli_num_rows($businessList); $i++) {
            if($row = mysqli_fetch_array($businessList)) {
                print '<li><a href="business.php?BusinessID=' . $row['BusinessID'] . '">' . $row['BusinessName'] . '</a></li>';
            }
        }

    }

    /****************************************************************************************/
    // Query to Push Business

    function pushBusiness($businessID,$businessName,$primaryContact,$primaryPhoneNumber,$notes,$street1,$street2,$zip_code) {
        include('includes/mysqli_connect.php');

        $valid = true;
        /* Add Validation Code here*/
        /* Print Errors for correction.  Changes will still display on the page but are not committed to the database if this function returns false*/

        if ($valid) {
            if ($businessID > 0) { // edits business
                $updateQuery = "UPDATE tbusiness
                                SET BusinessName = '$businessName'
                                   ,PrimaryContact = '$primaryContact'
                                   ,`PrimaryPhone#` = '$primaryPhoneNumber'
                                   ,Notes = '$notes'
                                WHERE BusinessID = $businessID";
                if (mysqli_query($dbc, $updateQuery)) {
                    $updateQuery = "SELECT ZipsID
                                    FROM tzips
                                    WHERE zip_code = $zip_code";
                    if ($zip = mysqli_query($dbc, $updateQuery)) {
                        $row = mysqli_fetch_array($zip);
                        $zipID = $row['ZipsID'];
                        $updateQuery = "UPDATE taddress
                                        SET Street1 = '$street1'
                                           ,Street2 = '$street2'
                                           ,ZipsID = '$zipID'
                                        WHERE BusinessID = $businessID";
                        if (mysqli_query($dbc, $updateQuery)) {
                            print'<p>Record Updated</p>';
                            return array(true, $businessID);
                        }
                    }
                }
            } else { // adds business
                $updateQuery = "INSERT INTO tbusiness
                                (BusinessName,PrimaryContact,`PrimaryPhone#`,Notes)
                                VALUES (\"$businessName\",\"$primaryContact\",\"$primaryPhoneNumber\",\"$notes\")";
                if (mysqli_query($dbc, $updateQuery)) {
                    $updateQuery = "SELECT BusinessID
                                    FROM tbusiness
                                    ORDER BY BusinessID DESC LIMIT 1";
                    if ($business = mysqli_query($dbc, $updateQuery)){
                        $row = mysqli_fetch_array($business);
                        $businessID = $row['BusinessID'];
                        $updateQuery = "SELECT ZipsID
                                        FROM tzips
                                        WHERE zip_code = $zip_code";
                        if ($zip = mysqli_query($dbc, $updateQuery)) {
                            $row = mysqli_fetch_array($zip);
                            $zipID = $row['ZipsID'];
                            $updateQuery = "INSERT INTO taddress
                                            (BusinessID,Street1,Street2,ZipsID)
                                            VALUES ($businessID,\"$street1\",\"$street2\",$zipID)";
                            if (mysqli_query($dbc, $updateQuery)) {
                                print'<p>Record Added</p>';
                                return array(true, $businessID);
                            }
                        }
                    }
                }
            }
        }
        print'<p>Record NOT Updated</p>';
        return array(False, $businessID);
    }

    /****************************************************************************************/
    // Query to Pull Business

    function pullBusiness($businessID){
        $businessQuery = "SELECT BusinessName, PrimaryContact, `PrimaryPhone#`, Notes
                  FROM tbusiness WHERE BusinessID = $businessID";

        return $businessQuery;
        //return mysql_query($businessQuery);

    }
    /****************************************************************************************/
    // Display Employee List

    function displayEmployeeList()
    {
        include('includes/mysqli_connect.php');
        $searchString = $_GET['Search'];
        $employeeListQuery = "SELECT EmployeeID, FirstName, LastName
                              FROM temployee
                              WHERE Firstname like '%$searchString%' or LastName like '%$searchString%'";

        $employeeList = mysqli_query($dbc, $employeeListQuery) or die("Error: ".mysqli_error($dbc));
        for ($i=0; $i <= mysqli_num_rows($employeeList); $i++) {
            if($row = mysqli_fetch_array($employeeList)) {
                print '<li><a href="employee.php?EmployeeID=' . $row['EmployeeID'] . '">' . $row['FirstName'] . ' ' . $row['LastName'] . '</a></li>';
            }
        }
    }

    /****************************************************************************************/
    // Query to Push Employee

    function pushEmployee($businessID,$employeeID,$jobTitle,$titleID,$firstName,$lastName,$phoneNumber,$extension,$email,$personalNote) {
        include('includes/mysqli_connect.php');

        $valid = true;
        /* Add Validation Code here*/
        /* Print Errors for correction.  Changes will still display on the page but are not committed to the database if this function returns false*/

        if ($valid) {
            if ($employeeID > 0) { // edits business
                $updateQuery = "UPDATE temployee
                                SET JobTitle = '$jobTitle'
                                   ,TitleID = $titleID
                                   ,FirstName = '$firstName'
                                   ,LastName = '$lastName'
                                   ,PhoneNumber = '$phoneNumber'
                                   ,Extension = '$extension'
                                   ,Email = '$email'
                                   ,PersonalNote = '$personalNote'
                                WHERE EmployeeID = $employeeID";
                if (mysqli_query($dbc, $updateQuery)) {
                    print'<p>Record Updated</p>';
                    return array(true, $employeeID);
                }
            } else { // adds business
                $updateQuery = "INSERT INTO temployee
                                (BusinessID,Active,JobTitle,TitleID,FirstName,LastName,PhoneNumber,Extension,Email,PersonalNote)
                                VALUES ($businessID,1,\"$jobTitle\",$titleID,\"$firstName\",\"$lastName\",\"$phoneNumber\",\"$extension\",\"$email\",\"$personalNote\")";
                    if (mysqli_query($dbc, $updateQuery)) {
                        $updateQuery = "SELECT EmployeeID
                                    FROM temployee
                                    ORDER BY EmployeeID DESC LIMIT 1";
                        if ($employee = mysqli_query($dbc, $updateQuery)) {
                            $row = mysqli_fetch_array($employee);
                            $employeeID = $row['EmployeeID'];
                            print'<p>Record Added</p>';
                            return array(true, $employeeID);
                        }
                }
            }
        }
        print'<p>Record NOT Updated</p>';
        return array(False, $employeeID);
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

        $userActionItemsQuery = pullUserActionItems($userID);

        if($userActionItems = mysqli_query($dbc, $userActionItemsQuery)) {
            if(mysqli_num_rows($userActionItems) == 0) {
                print '<p style="color:red">You do not have any Action Items at this time.</p>';
            } else {
                $numberOfActionItems = mysqli_num_rows($userActionItems);

                print "<ul><li><h4>Total Action Items: $numberOfActionItems</h4></li></ul>";
            }
        }


		print "<h3>Recent Contacts:</h3>";
		// Pull 
		$userNotesQuery = pullUserNotes($userID);
		
		if($userNotes = mysqli_query($dbc, $userNotesQuery)) {
            if (mysqli_num_rows($userNotes) == 0) {
                print '<p style="color:red">You do not have any notes stored in the system.</p>';
            } else {

                $numberOfNotes = mysqli_num_rows($userNotes);

                //print "<table id='notesTable'>";
                for ($i = 1; $i <= $numberOfNotes && $i <= 5; $i++) {
                    if ($row = mysqli_fetch_array($userNotes)) {
                        $datetime = strtotime($row['DateTime']);
                        $datetime = date("m/d/Y h:i a", $datetime);

                        print "
                            <ul class='recentContacts'>
                                <li>
                                    <a href='#' id='expandRow$i' style='color: #E00122'>Note $i</a>
                                    <b>Business: </b><a href='business.php?BusinessID=" . $row['BusinessID'] . "'>" . $row['BusinessName'] . "</a>&nbsp&nbsp&nbsp&nbsp&nbsp
                                    <b>Date:</b> " . $datetime . "
                                </li>
                                <div class=DashNote$i style='display:none;'>
                                    <ul>
                                        <li><b>Employee:</b> <a href='employee.php?EmployeeID=" . $row['employeeID'] . "'>" . $row['FirstName'] . " " . $row['LastName'] . "</a></li>
                                            <ul>
                                                <li><b>Phone #:</b> " . $row['Phone'] . " ext: " . $row['Ext'] . "</li>
                                                <li><b>Email:</b> <a href='mailto:" . $row['Email'] . "'>" . $row['Email'] . "</a></li>
                                            </ul>
                                        <li><b>Interaction Type:</b> " . $row['InteractionType'] . "</li>
                                        <li><b>Notes:</b><br /><div class='notes'> " . $row['Note'] . "</div></li>
                                    </ul>
                                </div>
                            </ul>
                        ";
                    } else {
                    }
                }
                print "<a href='#' id='allContacts' style='color: #E00122; text-align: center;'>Toggle All Contacts</a>";
                print "<div class='allNotes' style='display:none;'>";
                for ($i = 6; $i <= $numberOfNotes; $i++) {
                    if ($row = mysqli_fetch_array($userNotes)) {
                        $datetime = strtotime($row['DateTime']);
                        $datetime = date("m/d/Y h:i a", $datetime);

                        print "
                            <ul class='recentContacts'>
                                <li>
                                    <a href='#' id='expandRow$i' style='color: #E00122'>Note $i</a>
                                    <b>Business: </b><a href='business.php?BusinessID=" . $row['BusinessID'] . "'>" . $row['BusinessName'] . "</a>&nbsp&nbsp&nbsp&nbsp&nbsp
                                    <b>Date:</b> " . $datetime . "
                                </li>
                                <div class=DashNote$i style='display:none;'>
                                    <ul>
                                        <li><b>Employee:</b> <a href='employee.php?EmployeeID=" . $row['employeeID'] . "'>" . $row['FirstName'] . " " . $row['LastName'] . "</a></li>
                                            <ul>
                                                <li><b>Phone #:</b> " . $row['Phone'] . " ext: " . $row['Ext'] . "</li>
                                                <li><b>Email:</b> <a href='mailto:" . $row['Email'] . "'>" . $row['Email'] . "</a></li>
                                            </ul>
                                        <li><b>Interaction Type:</b> " . $row['InteractionType'] . "</li>
                                        <li><b>Notes:</b><br /><div class='notes'> " . $row['Note'] . "</div></li>
                                    </ul>
                                </div>
                            </ul>
                        ";
                    }
                }
                print "</div>";
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
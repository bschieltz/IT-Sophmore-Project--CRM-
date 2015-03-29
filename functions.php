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
	function pullUserNotes($subjectID, $subject) {
        if ($subject == "UserID") {
            $subject = "tuser.userID";
        } elseif ($subject == "BusinessID") {
            $subject = "tbusiness.BusinessID";
        } elseif ($subject = "EmployeeID") {
            $subject = "temployee.EmployeeID";
        }
		// Query to pull all contacts
		$userNotesQuery = "SELECT tuser.UserID, tuser.FirstName as 'UserFirstName', tuser.LastName as 'UserLastName', InteractionType, Note, tbusiness.BusinessID as 'BusinessID',
            BusinessName, temployee.EmployeeID as 'employeeID', temployee.FirstName as 'FirstName',
            temployee.LastName as 'LastName', temployee.PhoneNumber as 'Phone', temployee.Extension as 'Ext',
            temployee.Email as 'Email', personalNote, tnote.DateTime as 'NoteCreated', tnote.NoteID
			FROM tuser
				Right JOIN tnote
					ON tuser.userID = tnote.userID
				Right JOIN tinteractiontype
					ON tnote.interactiontypeID = tinteractiontype.interactiontypeID
                Right JOIN tbusiness
                    ON tnote.businessID = tbusiness.businessID
                Right JOIN temployee
                    ON tnote.employeeID = temployee.employeeID
			WHERE $subject = $subjectID
			Order By NoteCreated desc";

//        print $userNotesQuery;
		return $userNotesQuery;
	}

    /****************************************************************************************/
    // Pulling Action Items By User for use on the Dashboard
    function pullUserActionItems($searchID,$subject) {
        if ($subject == "UserID") {
            $subject = "AssignedToUserID";
        } elseif ($subject == "BusinessID") {
            $subject = "tbusiness.BusinessID";
        } elseif ($subject = "EmployeeID") {
            $subject = "temployee.EmployeeID";
        }

        // Query to pull all uncompleted action items
        $userActionItemsQuery = "
            SELECT tuser.UserID, tuser.FirstName as 'UserFirstName', tuser.LastName as 'UserLastName', InteractionType, Note, tbusiness.BusinessID as 'BusinessID',
                BusinessName, temployee.employeeID as 'employeeID', temployee.FirstName as 'FirstName',
                temployee.LastName as 'LastName', temployee.PhoneNumber as 'Phone', temployee.Extension as 'Ext',
                temployee.Email as 'Email', personalNote as 'EmployeeNote', tnote.DateTime as 'NoteCreated',
                tactionitem.DateTime as 'ActionItemCreated', ActionItemID, OriginalActionItemID, ReferanceID,
                AssignedToUserID, tactionitem.NoteID as 'NoteID', actionComplete
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
            Where $subject = $searchID
            Order By ActionItemCreated DESC;
            ";

        return $userActionItemsQuery;
    }
    /****************************************************************************************/
    // Pull all items associated to a given Action Item
    function pullAssocActionItems($OriginalActionItemID, $NoteID) {
        $OriginalActionItemID = $OriginalActionItemID;
        $NoteID = $NoteID;

        // Query to pull all the associated Action Items
        $assocActionItemsQuery = "
            SELECT tactionitem.NoteID, tactionitem.AssignedToUserID, tactionitem.originalactionitemID, tactionitem.DateTime as 'AIDate',
              tnote.UserID as 'PreviousUserID', tuser.FirstName as 'pUserFirstName', tuser.LastName as 'pUserLastName',tnote.Note as 'Note'
            FROM tactionitem
              JOIN tnote
                ON tactionitem.NoteID = tnote.NoteID
              JOIN tuser
                ON tnote.UserID = tuser.UserID
            WHERE tactionitem.OriginalActionItemID = $OriginalActionItemID
                AND tactionitem.NoteID < $NoteID
            ORDER BY AIDate desc
        ";

        return $assocActionItemsQuery;
    }




    /****************************************************************************************/
    // pull Title List

    function pullTitles() {
        require('includes/mysqli_connect.php');
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
    // get specific title

    function getTitle ($titleID) {
        require('includes/mysqli_connect.php'); // connect to database
        $titleQuery = "SELECT Title
                  FROM ttitle WHERE TitleID = $titleID";
        if ($titleResult = mysqli_query($dbc, $titleQuery)) {
            $row = mysqli_fetch_array($titleResult);
            return $row['Title'];
        }
        return "";
    }

    /****************************************************************************************/
    // pull interaction types list
    function pullInteractionTypes() {
        require('includes/mysqli_connect.php'); // connect to database
        $interactionTypeList = [];
        $interactionTypeQuery = "SELECT InteractionType, InteractionTypeID FROM tinteractiontype";
        if ($interactionTypeResult = mysqli_query($dbc, $interactionTypeQuery)) {
            for ($i=0; $i < mysqli_num_rows($interactionTypeResult); $i++) {
                if($row = mysqli_fetch_array($interactionTypeResult)) {
                    array_push($interactionTypeList, array($row['InteractionType'],$row['InteractionTypeID']));
                }
            }
        }
        return $interactionTypeList;
}
    /****************************************************************************************/
    // get specific interaction type

    function getInteractionType ($interactionTypeID) {
        require('includes/mysqli_connect.php'); // connect to database
        $interactionQuery = "SELECT InteractionType
                        FROM tinteractiontype
                        WHERE InteractionTypeID = $interactionTypeID";
        if ($interactionResult = mysqli_query($dbc, $interactionQuery)) {
            $row = mysqli_fetch_array($interactionResult);
            return $row['InteractionType'];
        }
        return "";
    }

    /****************************************************************************************/
    // Display Business List
    // Used for searching, grabs search from URL GET

    function displayBusinessList()
    {
        require('includes/mysqli_connect.php'); // connect to database
        $searchString = $_GET['Search']; // get search string
        $businessListQuery = "SELECT BusinessID, BusinessName
                  FROM tbusiness
                  WHERE BusinessName like '%$searchString%'";

        $businessList = mysqli_query($dbc, $businessListQuery) or die("Error: ".mysqli_error($dbc));
        for ($i=0; $i <= mysqli_num_rows($businessList); $i++) { // repeat for each business matching search, create unordered list
            if($row = mysqli_fetch_array($businessList)) {
                print '<li><a href="business.php?BusinessID=' . $row['BusinessID'] . '">' . $row['BusinessName'] . '</a></li>';
            }
        }

    }

    /****************************************************************************************/
    // Query to Push Business
    // used for both edit and add functions

    function pushBusiness($businessID,$businessName,$primaryContact,$primaryPhoneNumber,$notes,$street1,$street2,$zip_code)
    {
        require('includes/mysqli_connect.php'); // connect to database

        $valid = true;
        /* Add Validation Code here*/
        /* Print Errors for correction.  Changes will still display on the page but are not committed to the database if this function returns false*/
        // validate business name
        if (!isset($businessName)) {
            print"Error: enter a valid business name<br>";
            $valid = false;
        }
        //validate primary contact
        if (!isset($primaryContact)) {
            print"Error: enter a primary contact<br>";
            $valid = false;
        }
        // validate phone number.
        if (!isset($primaryPhoneNumber)) {
            print"Error: enter a primary phone number<br>";
            $valid = false;
        } // else if (regular expression match, phone num) { strip all non-numeric }
        // validate notes.
        if (!isset($notes)) {
            print"Error: please enter notes<br>";
            $valid = false;
        }
        // validate address. TODO: Not sure if street2 is optional!
        if (!isset($street1)) {
            print"Error: enter a street address<br>";
            $valid = false;
        }
        // may be optional, but not sure if database accepts NULL values
        if (!isset($street2)) {
            print"Error: please enter a complete street address<br>";
            $valid = false;
        }
        // validate zip
        if (!isset($zip_code)) {
            print"Error: please enter a valid zip code<br>";
            $valid = false;
        } else if (!preg_match('/[0-9]{5}([- ]?[0-9]{4})?$/', $zip_code)) {
            print"Error: please enter a valid zip code<br>";
            $valid = false;
        }
        // validate city
        if (!isset($city)) {
            print"Error: please enter a city<br>";
            $valid = false;
        }
        // validate state prefix
        if (!isset($statePrefix)) {
            print"Error: please enter a two-letter state prefix";
            $valid = false;
        } else if (!preg_match('/\b[A-Z]{2}/', $statePrefix)) {
            print"Error: please enter a two-letter state prefix";
        $valid = false;
        }
        // TODO: query database to ensure current form submission is unique and not duplicate!
        if ($valid) { // passed validation
            if ($businessID > 0) { // if there is a business id then it is an edit submission
                $updateQuery = "UPDATE tbusiness
                                SET BusinessName = '$businessName'
                                   ,PrimaryContact = '$primaryContact'
                                   ,`PrimaryPhone#` = '$primaryPhoneNumber'
                                   ,Notes = '$notes'
                                WHERE BusinessID = $businessID";
                if (mysqli_query($dbc, $updateQuery)) { // if update is successful, find the zip code id for address updating
                    $updateQuery = "SELECT ZipsID
                                    FROM tzips
                                    WHERE zip_code = $zip_code";
                    if ($zip = mysqli_query($dbc, $updateQuery)) { // if zip code id found update address
                        $row = mysqli_fetch_array($zip);
                        $zipID = $row['ZipsID'];
                        $updateQuery = "UPDATE taddress
                                        SET Street1 = '$street1'
                                           ,Street2 = '$street2'
                                           ,ZipsID = '$zipID'
                                        WHERE BusinessID = $businessID";
                        if (mysqli_query($dbc, $updateQuery)) { // return true
                            print'<p>Record Updated</p>';
                            return array(true, $businessID);
                        }
                    }
                }
            } else { // adds business
                $updateQuery = "INSERT INTO tbusiness
                                (BusinessName,PrimaryContact,`PrimaryPhone#`,Notes)
                                VALUES (\"$businessName\",\"$primaryContact\",\"$primaryPhoneNumber\",\"$notes\")";
                if (mysqli_query($dbc, $updateQuery)) { // if add is true then grab new business id based on last record added to database
                    $updateQuery = "SELECT BusinessID
                                    FROM tbusiness
                                    ORDER BY BusinessID DESC LIMIT 1";
                    if ($business = mysqli_query($dbc, $updateQuery)){ // if business found then grab ID and lookup zip code id
                        $row = mysqli_fetch_array($business);
                        $businessID = $row['BusinessID'];
                        $updateQuery = "SELECT ZipsID
                                        FROM tzips
                                        WHERE zip_code = $zip_code";
                        if ($zip = mysqli_query($dbc, $updateQuery)) {  // use zip id to add new address to database
                            $row = mysqli_fetch_array($zip);
                            $zipID = $row['ZipsID'];
                            $updateQuery = "INSERT INTO taddress
                                            (BusinessID,Street1,Street2,ZipsID)
                                            VALUES ($businessID,\"$street1\",\"$street2\",$zipID)";
                            if (mysqli_query($dbc, $updateQuery)) { // if everything is added then return true
                                print'<p>Record Added</p>';
                                return array(true, $businessID);
                            }
                        }
                    }
                }
            }
        }

        // defaults to false.  Only reaches this line if neither return true triggers
        print'<p>Record NOT Updated</p>';
        return array(False, $businessID);
    }

    /****************************************************************************************/
    // Query to Pull Business
    // only sets up query, does not call it

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
        require('includes/mysqli_connect.php'); // connect to database
        $searchString = $_GET['Search'];
        $employeeListQuery = "SELECT EmployeeID, FirstName, LastName, concat(FirstName,' ',LastName) as FullName
                              FROM temployee
                              HAVING FullName like '%$searchString%'";

        $employeeList = mysqli_query($dbc, $employeeListQuery) or die("Error: ".mysqli_error($dbc));
        if (mysqli_num_rows($employeeList) > 0) {
            for ($i=0; $i <= mysqli_num_rows($employeeList); $i++) {
                if($row = mysqli_fetch_array($employeeList)) {
                    print '<li><a href="employee.php?EmployeeID=' . $row['EmployeeID'] . '">' . $row['FirstName'] . ' ' . $row['LastName'] . '</a></li>';
                }
            }
        } else {
            print '<p>No Results Found</p>';
        }
    }

    /****************************************************************************************/
    // Query to Flip Active Status of an employee or user
    function flipActive($active,$idType/*"user" or "employee"*/,$ID){
        require('includes/mysqli_connect.php'); // connect to database
        $active = ($active == 1 ? 0 : 1);
        if ($idType == "user") {
            $updateQuery = "UPDATE tuser
                        SET Active = $active
                        WHERE UserID = $ID";
        } elseif ($idType == "employee") {
            $updateQuery = "UPDATE temployee
                        SET Active = $active
                        WHERE EmployeeID = $ID";
        }
        mysqli_query($dbc, $updateQuery);
    }

    /****************************************************************************************/
    // Query to Push Employee
    // Similar to business push, updates or adds employee based employeeID being zero or greater

    function pushEmployee($businessID,$employeeID,$jobTitle,$titleID,$firstName,$lastName,$phoneNumber,$extension,$email,$personalNote) {
        require('includes/mysqli_connect.php'); // connect to database

        $valid = true;
        /* Add Validation Code here*/
        /* Print Errors for correction.  Changes will still display on the page but are not committed to the database if this function returns false*/

        if ($valid) {
            if ($employeeID > 0) { // edits employee if employeeID is greater than zero
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
                if (mysqli_query($dbc, $updateQuery)) { //if successful
                    print'<p>Record Updated</p>';
                    return array(true, $employeeID);
                }
            } else { // adds employee
                $updateQuery = "INSERT INTO temployee
                                (BusinessID,Active,JobTitle,TitleID,FirstName,LastName,PhoneNumber,Extension,Email,PersonalNote)
                                VALUES ($businessID,1,\"$jobTitle\",$titleID,\"$firstName\",\"$lastName\",\"$phoneNumber\",\"$extension\",\"$email\",\"$personalNote\")";
                    if (mysqli_query($dbc, $updateQuery)) {  // if successful get employee by looking up most recent record added to employee table
                        $updateQuery = "SELECT EmployeeID
                                    FROM temployee
                                    ORDER BY EmployeeID DESC LIMIT 1";
                        if ($employee = mysqli_query($dbc, $updateQuery)) { // use found employee id for return array
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
    // Display User List

    function displayUserList()
    {
        require('includes/mysqli_connect.php'); // connect to database
        $searchString = $_GET['Search'];
        $userListQuery = "SELECT UserID, FirstName, LastName, concat(FirstName,' ',LastName) as FullName
                              FROM tuser
                              HAVING FullName like '%$searchString%'";

        $userList = mysqli_query($dbc, $userListQuery) or die("Error: ".mysqli_error($dbc));
        if (mysqli_num_rows($userList) > 0) {
            for ($i=0; $i <= mysqli_num_rows($userList); $i++) {
                if($row = mysqli_fetch_array($userList)) {
                    print '<li><a href="user.php?UserID=' . $row['UserID'] . '">' . $row['FirstName'] . ' ' . $row['LastName'] . '</a></li>';
                }
            }
        } else {
            print '<p>No Results Found</p>';
        }
    }

/****************************************************************************************/
// Query to Push User
// Similar to business push, updates or adds user based userID being zero or greater

    function pushUser($userID,$titleID,$firstName,$lastName,$email,$admin,$phoneNumber,$interactionTypeID,$password1,$password2) {
        require('includes/mysqli_connect.php'); // connect to database

        $valid = true;
        /* Add Validation Code here*/
        /* Print Errors for correction.  Changes will still display on the page but are not committed to the database if this function returns false*/
        if ($password1 != "" || $password2 != "") {
            if ($password1 != $password2) {
                $valid = false;
                print "Your passwords do not match, please try again.";
            }
        }

        if ($valid) {
            if ($userID > 0) { // edits user if $userID is greater than zero
                $updateQuery = "UPDATE tuser
                                SET TitleID = $titleID
                                   ,FirstName = '$firstName'
                                   ,LastName = '$lastName'
                                   ,Email = '$email'
                                   ,Admin = $admin
                                   ,PhoneNumber = '$phoneNumber'
                                   ,InteractionTypeID = '$interactionTypeID' " .
                                    ($password1 != "" ? ",Password = '$password1'" : "") .
                                " WHERE UserID = $userID";
                if (mysqli_query($dbc, $updateQuery)) { //if successful
                    print'<p>Record Updated</p>';
                    return array(true, $userID);
                }
            } else { // adds user
                $updateQuery = "INSERT INTO tuser
                                (Active,TitleID,FirstName,LastName,Email,Admin,PhoneNumber,InteractionTypeID,Password)
                                VALUES (1,$titleID,\"$firstName\",\"$lastName\",\"$email\",\"$admin\",\"$phoneNumber\",\"$interactionTypeID\",\"$password1\")";
                if (mysqli_query($dbc, $updateQuery)) {  // if successful get user by looking up most recent record added to user table
                    $updateQuery = "SELECT UserID
                                    FROM tuser
                                    ORDER BY UserID DESC LIMIT 1";
                    if ($user = mysqli_query($dbc, $updateQuery)) { // use found user id for return array
                        $row = mysqli_fetch_array($user);
                        $userID = $row['UserID'];
                        print'<p>Record Added</p>';
                        return array(true, $userID);
                    }
                }
            }
        }
        print'<p>Record NOT Updated</p>'. $updateQuery;
        return array(False, $userID);
    }

    /****************************************************************************************/
	// Build Dashboard
	function dashboard($userID, $userFullName) {
        require('includes/mysqli_connect.php'); // connect to database
		
		if($_SESSION["userAuth"] != "1") {
			noAuth();
		}
		
		print "<h2 style='color: #E00122;'>Welcome, $userFullName!</h2>";

        print "<br /><form action='notes.php' method='get'><input type='submit' value='Add New Interaction'  class='myButton'/></form>";
        print "<form action='http://homepages.uc.edu/group1/business.php?CreateBusiness=True'><input type='submit' value='Add New Business'  class='myButton'/></form><br />";

		print "<br /><br />";
        $actionItems = new Interactions();
        $actionItems->setUserID($userID);
        $actionItems->printActionItems();
        $actionItems->printNotes();

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
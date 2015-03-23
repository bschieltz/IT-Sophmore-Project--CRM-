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
            Where AssignedToUserID = $userID
                AND actionComplete is NULL
            Order By 'ActionItemCreated' desc;
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
                AND tactionitem.NoteID != $NoteID
            ORDER BY AIDate desc
        ";

        return $assocActionItemsQuery;
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
    // Used for searching, grabs search from URL GET

    function displayBusinessList()
    {
        include('includes/mysqli_connect.php'); // connect to database
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

    function pushBusiness($businessID,$businessName,$primaryContact,$primaryPhoneNumber,$notes,$street1,$street2,$zip_code) {
        include('includes/mysqli_connect.php');

        $valid = true;
        /* Add Validation Code here*/
        /* Print Errors for correction.  Changes will still display on the page but are not committed to the database if this function returns false*/

        if ($valid) {
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
        include('includes/mysqli_connect.php');
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
    // Query to Flip Active Status of an employee
    function flipActive($active,$employeeID){
        $active = ($active == 1 ? 0 : 1);
        $updateQuery = "UPDATE temployee
                        SET Active = $active
                        WHERE EmployeeID = $employeeID";
        mysqli_query($dbc, $updateQuery) or die("Error: ". $updateQuery);
    }

    /****************************************************************************************/
    // Query to Push Employee
    // Similar to business push, updates or adds employee based employeeID being zero or greater

    function pushEmployee($businessID,$employeeID,$jobTitle,$titleID,$firstName,$lastName,$phoneNumber,$extension,$email,$personalNote) {
        include('includes/mysqli_connect.php');

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
	// Build Dashboard
	function dashboard($userID, $userFullName) {
		include('includes/mysqli_connect.php');
		
		if($_SESSION["userAuth"] != "1") {
			noAuth();
		}
		
		print "<h2 style='color: #E00122;'>Welcome, $userFullName!</h2>";

        print "<br /><form action='notes.php' method='get'><input type='submit' value='Add New Interaction'  class='myButton'/></form>";
        print "<form action='http://homepages.uc.edu/group1/business.php?CreateBusiness=True'><input type='submit' value='Add New Business'  class='myButton'/></form><br />";

		print "<br /><br />";
		
		print "<h3>Current Action Items:</h3>";
		
		/***************************** Action Items  ************************************/
        // Store Action Items query to variable
        $userActionItemsQuery = pullUserActionItems($userID);

        // Run Action Items query
        if($userActionItems = mysqli_query($dbc, $userActionItemsQuery)) {
            if(mysqli_num_rows($userActionItems) == 0) { // If no action items are present, print statement
                print '<p style="color:red">You do not have any Action Items at this time.</p>';
            } else {
                $numberOfActionItems = mysqli_num_rows($userActionItems);

                print "<h4 style='padding-left: 25px;'>Total Action Items: <b>$numberOfActionItems</b></h4>";

                for($i=1; $i<=$numberOfActionItems; $i++) {
                    if ($row = mysqli_fetch_array($userActionItems)) {

                        // Convert DateTime to something usable
                        $actionDateTime = strtotime($row['ActionItemCreated']);
                        $actionDateTime = date("m/d/Y h:i a", $actionDateTime);

                        print "
                            <ul class='actionItemsList'>
                                <li>
                                    <b><a href='#' id='ExpandAI$i' class='AIClass' style='color: #E00122'>Action Item $i</a></b>
                                    <b>Business: </b><a href='business.php?BusinessID=" . $row['BusinessID'] . "'>" . $row['BusinessName'] . "</a><br />
                                    <div style='text-align: center;'><b>Date:</b> " . $actionDateTime . "</div>
                                </li>
                                <div id='toExpandAI$i' class='DashAI'>
                                    <ul>
                                        <li><b>Employee:</b> <a href='employee.php?EmployeeID=" . $row['employeeID'] . "'>" . $row['FirstName'] . " " . $row['LastName'] . "</a></li>
                                            <ul>
                                                <li><b>Phone #:</b> " . $row['Phone'] . " ext: " . $row['Ext'] . "</li>
                                                <li><b>Email:</b> <a href='mailto:" . $row['Email'] . "'>" . $row['Email'] . "</a></li>
                                            </ul>
                                        <li><b>Interaction Type:</b> " . $row['InteractionType'] . "</li>
                                        <li><b>Notes:</b><br /><div class='notes'> " . $row['Note'] . "</div>
                                            <h4 style='width: 75%; margin-left: auto; margin-right: auto; text-align: center;'>
                                                <!-- Need to add Links -->
                                                <a href=''>Add Note</a> |
                                                <a href=''>Forward</a> |
                                                <a href=''>Mark Complete</a>
                                            </h4>
                                        </li>
                        "; //style='display:none;'

                        print "<li><b>Item History: </b>";

                        // Pull all associtated Action Item Data
                        $OriginalActionItemID = $row['OriginalActionItemID'];
                        $NoteID = $row['NoteID'];

                        $assocActionItemsQuery = pullAssocActionItems($OriginalActionItemID, $NoteID);

                        if($assocActionItems = mysqli_query($dbc, $assocActionItemsQuery)) {
                            $numHistoryItems = mysqli_num_rows($assocActionItems);
                            if(mysqli_num_rows($assocActionItems) == 0) {
                                print 'You do not have any Action Items at this time.</li>';
                            } else {
                                print "This Action Item $numHistoryItems history items.</li>";
                                for($j=1; $j <= $numHistoryItems; $j++) {
                                    if($assocRow = mysqli_fetch_array($assocActionItems)) {
                                        // Convert DateTime to something usable
                                        $AIDateTime = strtotime($assocRow['AIDate']);
                                        $AIDateTime = date("m/d/Y h:i a", $AIDateTime);
                                        $pUserName = $assocRow['pUserFirstName'] . " " . $assocRow['pUserLastName'];

                                        // Print History items related to this action item
                                        print "
                                            <ul class='actionItemsList'>
                                                <a href='#' id='ExpandAIH$i$j' class='AIHClass' style='color: #E00122;'>History Item $j</a>
                                                <div id='toExpandAIH$i$j' class='DashAI' style='display: none;'>
                                                    <li><b>User:</b> $pUserName &nbsp&nbsp&nbsp <b>Date:</b> $AIDateTime</li>
                                                    <li><b>Notes: </b><br /><div class='notes'> " . $assocRow['Note'] . "</div></li>
                                                </div>
                                            </ul>
                                        ";

                                    }
                                }
                            }
                        }

                        print "
                                    </ul>
                                </div>
                            </ul>
                        ";
                    }
                }
            }
        }
        else {
            print "ERROR IN ACTION ITEMS!";
        }

        print "<br /><hr /><hr /><hr /><hr /><br />";
        /////////////////////////////////////////////////////////////////////////
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
                                    <a href='#' class='expandRow' id='DashRow$i' style='color: #E00122'>Note $i</a>
                                    <b>Business: </b><a href='business.php?BusinessID=" . $row['BusinessID'] . "'>" . $row['BusinessName'] . "</a><br />
                                    <div style='margin-left: 60px;'><b>Date:</b> " . $datetime . "</div>
                                </li>
                                <div class='DashNote' id='toDashRow$i' style='display:none;'>
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
                print "<a href='#' id='allContacts' style='color: #E00122; text-align: center;'>View All Contacts</a>";
                print "<div class='allNotes' style='display:none;'>";
                for ($i = 6; $i <= $numberOfNotes; $i++) {
                    if ($row = mysqli_fetch_array($userNotes)) {
                        $datetime = strtotime($row['DateTime']);
                        $datetime = date("m/d/Y h:i a", $datetime);

                        print "
                            <ul class='recentContacts'>
                                <li>
                                    <a href='#' class='expandRow' id='DashRow$i' style='color: #E00122'>Note $i</a>
                                    <b>Business: </b><a href='business.php?BusinessID=" . $row['BusinessID'] . "'>" . $row['BusinessName'] . "</a><br />
                                    <div style='margin-left: 60px;'><b>Date:</b> " . $datetime . "</div>
                                </li>
                                <div class='DashNote' id='toDashRow$i' style='display:none;'>
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
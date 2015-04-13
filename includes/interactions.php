<?php
/**
 * Created by Brian. Started by Frank
 * User: admin
 * Date: 3/24/2015
 * Time: 10:25 PM
 */

class Interactions {
    // only one of these needs to be set by the object in order to call the functions
    private $userID = 0;
    private $businessID = 0;
    private $employeeID = 0;
    private $alreadyPrintedNotes = [];
    private $businessName = "";

    function getUserID(){return $this->userID;}
    function getBusinessID(){return $this->businessID;}
    function getEmployeeID(){return $this->employeeID;}
    function setUserID($userID){$this->userID = $userID;}
    function setBusinessID($businessID){
        require('includes/mysqli_connect.php'); // connect to database
        $this->businessID = $businessID;
        $businessQuery = "SELECT BusinessName, PrimaryContact, `PrimaryPhone#`, Notes
                  FROM tbusiness WHERE BusinessID = $this->businessID";
        if ($business = mysqli_query($dbc, $businessQuery)) {
            $row = mysqli_fetch_array($business);
            $this->businessName = $row['BusinessName'];
        }
    }
    function setEmployeeID($employeeID){$this->employeeID = $employeeID;}

    /****************************************************************************************/
    // Submit interaction
    function submitInteraction(){
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_GET['interactionSubmit'])) {
                $BusinessID = $_GET['BusinessID'];
                $UserID = $_GET['UserID'];
                $EmployeeID = $_GET['EmployeeID'];
                ($EmployeeID != 0 ?: $EmployeeID = 'NULL' );
                $InteractionTypeID = $_GET['InteractionTypeID'];
                $noteType = $_GET['noteType'];
                $Note = $_GET['Note'];

                if ($noteType == 'Note') {
                    if (!insertInteraction($UserID, $BusinessID, $EmployeeID, $InteractionTypeID, $Note)) {
                        print "Note Values:" . $UserID . " " . $BusinessID . " " . $EmployeeID . " " . $InteractionTypeID . " " . $Note;
                    }
                } else {
                    $AssignedToUserID = $_GET['AssignedToUserID'];
                    $OriginalActionItemID = $_GET['OriginalActionItemID'];
                    $ReferenceID = $_GET['ReferenceID'];
                    $CloseAction = 1;
                    if ($AssignedToUserID == 0) {
                        $AssignedToUserID = $UserID;
                    } else {
                        $CloseAction = 0;
                    }
                    if (!insertActionItem($UserID, $BusinessID, $EmployeeID, $InteractionTypeID, $Note, $OriginalActionItemID, $ReferenceID, $AssignedToUserID, $CloseAction)) {

                    }
                }
                //print_r($_GET);
            }
        }
    }

    /****************************************************************************************/
    // Print Edit Box
    private function printEditBox($type,$sentI,$userID,$businessID,$businessName,$employeeID,$interactionType,$OriginalActionItemID,$ReferenceID){
        require('includes/mysqli_connect.php'); // connect to database
        $userListQuery = "SELECT UserID, FirstName, LastName FROM tuser";
        $userList = mysqli_query($dbc, $userListQuery) or die("Error: ".mysqli_error($dbc));
        $employeeListQuery = "SELECT EmployeeID, FirstName, LastName FROM temployee WHERE BusinessID = $businessID";
        $employeeList = mysqli_query($dbc, $employeeListQuery) or die("Error: ".mysqli_error($dbc));
        $interactionList = pullInteractionTypes();

        print "<ul class='editBoxHeader' name='editBox$sentI'>";
            if ($userID == "")  {
                print'<li class="editNew"><a href="">Add New Interaction</a></li>';
            } else {
                print'<h4 style="width: 75%; margin-left: auto; margin-right: auto; text-align: center;">
                      <li class="editForwardClose"><a href="">Forward</a> | <a href=""> Close</a></li></h4>';
            }
        print'</ul>';
//        <input type='hidden' name='submitInteraction' value='true' />
        print"<form action='" . $_SERVER['PHP_SELF'] . "' class='editBoxContent displayOff' name='toeditBox$sentI' method='get'>
            <input type='hidden' name='BusinessID' value='$businessID'/>
            <input type='hidden' name='UserID' value='" . $_SESSION['userID'] . "'>
            <input type='hidden' name='OriginalActionItemID' value='$OriginalActionItemID'>
            <input type='hidden' name='ReferenceID' value='$ReferenceID'>
            Business: $businessName
            <div style='float:right;'>Involving: <select name='EmployeeID'>
                <option value='0'>None</option>";
                    if (mysqli_num_rows($employeeList) > 0) {
                        for ($i=0; $i <= mysqli_num_rows($employeeList); $i++) {
                            if($row = mysqli_fetch_array($employeeList)) {
                                print "<option value='" . $row['EmployeeID'] . "'" . ($row['EmployeeID'] == $employeeID ? ' Selected' : '') . ">" . $row['FirstName'] . " " . $row['LastName'] . "</option>";
                            }
                        }
                    }
                print"</select></div><br />
                    Interaction Type: <select name='InteractionTypeID'>";
                    for ($i=0; $i < sizeof($interactionList); $i++) {
                        print'<option value="' . $interactionList[$i][1] . '"' . ($interactionList[$i][0]==$interactionType ? ' Selected' : '') . '>' . $interactionList[$i][0] . '</option>' . "\r\n";
                    }
                print"</select>
            <div class='displayInline" . ($type != "note" ? " displayOff" : '') . "'>Type: <select class='InteractionSelection' ID='InteractionType$sentI' name='noteType'>
                <option value='Note'>Note</option>
                <option value='Action Item'" . ($type != "note" ? ' Selected' : '') . ">Action Item</option>
            </select></div>
            <div style='float:right;' class='displayInline ShowInteractionType$sentI" . ($type == "note" ? " displayOff" : "") . "'>
                Forward To: <select name='AssignedToUserID'>";
                if ($OriginalActionItemID != 0) {print"<option value='0'>Close</option>";}
                    if (mysqli_num_rows($userList) > 0) {
                        for ($i=0; $i <= mysqli_num_rows($userList); $i++) {
                            if($row = mysqli_fetch_array($userList)) {
                                print "<option value='" . $row['UserID'] . "'>" . $row['FirstName'] . " " . $row['LastName'] . "</option>";
                            }
                        }
                    }
                print"</select>
            </div>
            <textarea name='Note' rows='8' cols='40'></textarea>
            <input type='submit' value='Submit' name='interactionSubmit'/>
        </form>";
    }

    /****************************************************************************************/
    // Prints individual note / action item record
    private function printItem($i,$row,$headerType){
        $actionDateTime = strtotime($row['NoteCreated']);
        $actionDateTime = date("m/d/Y h:i a", $actionDateTime);
        $actionCompete = "";
        $OriginalActionItemID = $row['OriginalActionItemID'];
        $NoteID = $row['NoteID'];
        $originalDate = $row['ActionItemCreated'];
        $actionItemID = $row['ActionItemID'];

        if ($headerType == "action") {
            if (!is_null($row['actionComplete'])) {
                $actionCompete = " complete";
            }
        }
        array_push($this->alreadyPrintedNotes,$row['NoteID']);
        print "
            <ul class='actionItemsList'>
                <li class='interactionHeader $headerType $actionCompete'>
                    <a href='#' name='ExpandAI$headerType$i' class='AIClass'>
                        <div>" . $actionDateTime . " >> " . $row['BusinessName'] . " >> " . substr($row['Note'],0,20) . "</div>
                    </a>
                </li>
            </ul>
               <ul name='toExpandAI$headerType$i' class='DashAI displayOff $headerType'>
                    <li style='float:right;'><b>Employee:</b> <a href='employee.php?EmployeeID=" . $row['employeeID'] . "'>" . ($row['FirstName'] != '' ? $row['FirstName'] : 'None') . " " . $row['LastName'] . "</a></li>
                    <li><b>Business: </b><a href='business.php?BusinessID=" . $row['BusinessID'] . "'>" . $row['BusinessName'] . "</a></li>
                    <li style='float:right;'><b>Email:</b> <a href='mailto:" . $row['Email'] . "'>" . ($row['Email'] != '' ? $row['Email'] : 'None') . "</a></li>
                    <li><b>Phone #:</b> " . $row['Phone'] . " ext: " . $row['Ext'] . "</li>
                    <li style='float:right;'>
                        <b>" . ($headerType == "action" ? "Assigned To: " : "Created By: ") . "</b><a href='user.php?UserID=" . $row['UserID'] . "'>" . $row['UserFirstName'] . " " . $row['UserLastName'] . "</a></li>
                    <li><b>Interaction Type:</b> " . $row['InteractionType'] . "</li>
                    <li><div class='notes'> " . $row['Note'] . "</div>";
                        if (is_null($row['actionComplete'])) {
                            if ($row['actionComplete'] == NULL && $headerType == "action") {
                                if ($row['UserID'] == $_SESSION['userID']) {
                                    $this->printEditBox($headerType, $i, $row['UserID'], $row['BusinessID'], $row['BusinessName'], $row['employeeID'],$row['InteractionType'],$OriginalActionItemID,$actionItemID);
                                }
                            }
                        }
                    print"</li>
                </ul>
        ";

        if ($headerType == "action") { // if action item print history
            require('includes/mysqli_connect.php');
            // Pull all associated Action Item Data

            $assocActionItemsQuery = pullAssocActionItems($OriginalActionItemID, $NoteID, $originalDate);

            if($assocActionItems = mysqli_query($dbc, $assocActionItemsQuery)) {
                $numHistoryItems = mysqli_num_rows($assocActionItems);
                if(mysqli_num_rows($assocActionItems) == 0) {
                } else {
                    for($j=1; $j <= $numHistoryItems; $j++) {
                        if($assocRow = mysqli_fetch_array($assocActionItems)) {
                            // Convert DateTime to something usable
                            $AIDateTime = strtotime($assocRow['AIDate']);
                            $AIDateTime = date("m/d/Y h:i a", $AIDateTime);
                            $pUserName = $assocRow['pUserFirstName'] . " " . $assocRow['pUserLastName'];
                            array_push($this->alreadyPrintedNotes,$assocRow['NoteID']);

                            // Print History items related to this action item
                            print "
                                <ul name='toExpandAIaction$i' class='actionItemsList displayOff AIHClass'>
                                    <li><a href='#' name='ExpandAIH$i$j' class='AIHClass'>" . substr($assocRow['Note'],0,40) . "</a></li>
                                </ul>
                                <ul name='toExpandAIH$i$j' class='DashAI displayOff DashAIH toExpandAIaction$i'>
                                    <li><b>UC Staff:</b><a href='user.php?UserID=" . $assocRow['AssignedToUserID'] . "'> $pUserName &nbsp&nbsp&nbsp</a> <b>Date:</b> $AIDateTime</li>
                                    <li><b>Notes: </b><br /><div class='notes'> " . $assocRow['Note'] . "</div></li>
                                </ul>
                            ";
                        }
                    }
                }
            }
        }
    }

    /****************************************************************************************/
    // Print Action Items
    function printActionItems(){
        require('includes/mysqli_connect.php');
        // Store Action Items query to variable
        $actionItemsQuery = "";

        if ($this->userID > 0) {
            $actionItemsQuery = pullUserActionItems($this->userID,"UserID");
        } elseif ($this->businessID > 0) {
            $actionItemsQuery = pullUserActionItems($this->businessID,"BusinessID");
        } elseif ($this->employeeID > 0) {
            $actionItemsQuery = pullUserActionItems($this->employeeID,"EmployeeID");
        }
        // Run Action Items query
        if($userActionItems = mysqli_query($dbc, $actionItemsQuery)) {
            if(mysqli_num_rows($userActionItems) == 0) { // If no action items are present, print statement
                print '<p style="color:red">You do not have any Action Items at this time.</p>';
            } else {
                $numberOfActionItems = mysqli_num_rows($userActionItems);
                print "<h4 style='padding-left: 25px;'>Action Items: 1-<b>$numberOfActionItems</b></h4>";

                for($i=1; $i<=$numberOfActionItems; $i++) {
                    if ($row = mysqli_fetch_array($userActionItems)) {
                        if (!in_array($row['NoteID'],$this->alreadyPrintedNotes)) {
                            $this->printItem($i, $row, "action");
                        }
                    }
                }
            }
        }
        else {
            print "ERROR IN ACTION ITEMS! $actionItemsQuery";
        }
    }


    /****************************************************************************************/
    // Print Notes
    function printNotes(){
        require('includes/mysqli_connect.php');
        print "<h3>Recent Contacts:</h3>";
        // Pull
        $notesQuery = "";
//          print $this->userID . $this->businessID . $this->employeeID;
        if ($this->userID > 0) {
            $notesQuery = pullUserNotes($this->userID,"UserID");
        } elseif ($this->businessID > 0) {
            $notesQuery = pullUserNotes($this->businessID,"BusinessID");
        } elseif ($this->employeeID > 0) {
            $notesQuery = pullUserNotes($this->employeeID,"EmployeeID");
        }
        //print $notesQuery;


        if($notes = mysqli_query($dbc, $notesQuery)) {
            if (mysqli_num_rows($notes) == 0) {
                print '<p style="color:red">You do not have any notes stored in the system.</p>';
            } else {

                $numberOfNotes = mysqli_num_rows($notes);

                //print "<table id='notesTable'>";
                for ($i = 1; $i <= $numberOfNotes && $i <= 5; $i++) {
                    if ($row = mysqli_fetch_array($notes)) {
                        if (!in_array($row['NoteID'],$this->alreadyPrintedNotes)) {
                            $this->printItem($i, $row, "note");
                        }
                    } else {
                    }
                }
            }
        }
        else {
            print "<h3>ERROR!</h3>";
        }
    }

    /****************************************************************************************/
    // Print notes and action items mixed by most recent
    function printInteractions(){
        require('includes/mysqli_connect.php');
        // Store Action Items query to variable
        $actionItemsQuery = "";
        $name = "";

        if ($this->userID > 0) {
            $actionItemsQuery = pullInteractions($this->userID,"UserID");
            $name = "User";
        } elseif ($this->employeeID > 0) {
            $actionItemsQuery = pullInteractions($this->employeeID,"EmployeeID");
            $name = "Employee";
        } elseif ($this->businessID > 0) {
            $actionItemsQuery = pullInteractions($this->businessID,"BusinessID");
            $name = "Business";
        }

        if ($name != "") {
            if ($this->employeeID != "" || $this->businessID != ""){
                $this->printEditBox("note",0,"",$this->businessID,$this->businessName,$this->employeeID,"",0,0,0);
            }
            // Run Action Items query
            if ($userActionItems = mysqli_query($dbc, $actionItemsQuery)) {
                if (mysqli_num_rows($userActionItems) == 0) { // If no action items are present, print statement
                    print '<p style="color:red">No Interactions this ' . $name . '</p>';
                } else {
                    $numberOfActionItems = mysqli_num_rows($userActionItems);
                    //print "<h4 style='padding-left: 25px;'>Action Items: 1-<b>$numberOfActionItems</b></h4>";

                    for ($i = 1; $i <= $numberOfActionItems; $i++) {
                        if ($row = mysqli_fetch_array($userActionItems)) {
                            if (!in_array($row['NoteID'], $this->alreadyPrintedNotes)) {
                                if ($row['ActionItemID'] != NULL) {
                                    $this->printItem($i, $row, "action");
                                } else {
                                    $this->printItem($i, $row, "note");
                                }
                            }
                        }
                    }
                }
            } else {
                print "ERROR IN ACTION ITEMS! $actionItemsQuery";
            }
        }
    }
}
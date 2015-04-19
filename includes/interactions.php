<?php
/**
 * Created by Brian. Started by Frank
 * User: admin
 * Date: 3/24/2015
 * Time: 10:25 PM
 *
 * This class handles the displaying adding and responding to interactions on all pages
 * You can add interactions to any page by providing either a userID, businessID or EmployeeID using the following commands.
 *
 * $actionItems = new Interactions();
 * $actionItems->setUserID($userID);
 * $actionItems->submitInteraction();
 * $actionItems->printActionItems();
 */

class Interactions {
    // only one of these needs to be set by the object in order to call the functions
    private $userID = 0;
    private $businessID = 0;
    private $employeeID = 0;
    private $alreadyPrintedNotes = [];
    private $businessName = "";

    // set and get functions.  No known instances of get being used currently 04/18/15
    function getUserID(){return $this->userID;}
    function getBusinessID(){return $this->businessID;}
    function getEmployeeID(){return $this->employeeID;}
    function setUserID($userID){$this->userID = $userID;}

    // if setting businessID grab business name.  Used when populating 'add new interaction'
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
    // on postback submits new or responded to action item or note and then redirects the page back to itself.
    // this gives the database time to write across all tables and clears the address bar to prevent accidentally
    // re-entering the data.

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

                if ($noteType == 'Note') { // notes are always new
                    if (insertInteraction($UserID, $BusinessID, $EmployeeID, $InteractionTypeID, $Note)) {
                        //print "Note Values:" . $UserID . " " . $BusinessID . " " . $EmployeeID . " " . $InteractionTypeID . " " . $Note;
                        // redirect will always send the page back to itself, this just tells it which ID to load
                        if ($this->userID > 0) {
                            redirect("UserID=" . $this->userID, 5);
                        } elseif ($this->employeeID > 0) {
                            redirect("EmployeeID=" . $this->employeeID, 5);
                        } elseif ($this->businessID > 0) {
                            redirect("BusinessID=" . $this->businessID, 5);
                        }
                    }
                } else { // if not note then action item
                    $AssignedToUserID = $_GET['AssignedToUserID'];
                    $OriginalActionItemID = $_GET['OriginalActionItemID'];
                    $ReferenceID = $_GET['ReferenceID'];
                    $CloseAction = 1; // true

                    // the insertActionItem function handles whether it is a new, responded to, or closing action item.
                    // see function for details, however correct values for submitting the correctly desired outcome are
                    // defaulted in the form.  The exception is that is the form must set $AssignedToUserID to zero for closing
                    // or else we have no way of knowing if the user intends to close or just create a new action item
                    // assigned to themselves.
                    // if they choose close then AssignedToUserID must be their ID and CloseAction = 1.
                    if ($AssignedToUserID == 0) {
                        $AssignedToUserID = $UserID;
                    } else {
                        $CloseAction = 0; // false
                    }
                    if (insertActionItem($UserID, $BusinessID, $EmployeeID, $InteractionTypeID, $Note, $OriginalActionItemID, $ReferenceID, $AssignedToUserID, $CloseAction)) {
                        // redirect will always send the page back to itself, this just tells it which ID to load
                        if ($this->userID > 0) {
                            redirect("UserID=" . $this->userID, 5);
                        } elseif ($this->employeeID > 0) {
                            redirect("EmployeeID=" . $this->employeeID, 5);
                        } elseif ($this->businessID > 0) {
                            redirect("BusinessID=" . $this->businessID, 5);
                        }
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
        $userListQuery = "SELECT UserID, FirstName, LastName FROM tuser WHERE Active = 1";
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

        // this section prints some static information and populates the drop down boxes.
        // also decides which information to display based on note or action item and new or responding
        // also defaults drop boxes to correct value.  IE if you are on an employee page it will default to that employee
        print"<form action='" . $_SERVER['PHP_SELF'] . "' class='editBoxContent " . (!empty($_GET['AddNewInteraction']) && $sentI == 0 ?: 'displayOff') . "' name='toeditBox$sentI' ID='toeditBox$sentI' method='get'>
            <input type='hidden' name='BusinessID' value='$businessID'/>
            <input type='hidden' name='UserID' value='" . $_SESSION['userID'] . "'>
            <input type='hidden' name='OriginalActionItemID' value='$OriginalActionItemID'>
            <input type='hidden' name='ReferenceID' value='$ReferenceID'>
            Business: $businessName
            <div style='float:right;' class='displayInline'>Involving: <select name='EmployeeID'>
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

        // this array prevents an item from getting printed more than once.
        // this happens when a user is assigned an action item, forwards it to someone else
        // and then it gets forwarded back to themselves.  In this instance the original action
        // item assigned to them will show up as the first item in the history of the current one,
        // not as a separate action item on it's own line.
        // because the list is printed in DESC order the history items are always found first.
        array_push($this->alreadyPrintedNotes,$row['NoteID']);

        // this section prints the header, which is the visible part the user clicks to expand the information
        print "
            <ul class='actionItemsList'>
                <li class='interactionHeader $headerType $actionCompete'>
                    <a href='#' name='ExpandAI$headerType$i' class='AIClass'>
                        <div>" . $actionDateTime . " >> " . $row['BusinessName'] . " >> " . substr($row['Note'],0,20) . "</div>
                    </a>
                </li>
            </ul>";

        // this section prints the static information associated with each header.
        // every item is giving a unique name and ID.  The javascript grabs this unique name from the header
        // then adds 'to' to the front to find the corresponding collapsed tag in order to display it
        // the javascript uses the name field.  The ID tag exists so that when a user clicks new interaction
        // from the dashboard, only the correct 'add new interaction' expands.
        print"<ul name='toExpandAI$headerType$i' class='DashAI displayOff $headerType'>
                    <li style='float:right;'><b>Employee:</b> <a href='employee.php?EmployeeID=" . $row['employeeID'] . "'>" . ($row['FirstName'] != '' ? $row['FirstName'] : 'None') . " " . $row['LastName'] . "</a></li>
                    <li><b>Business: </b><a href='business.php?BusinessID=" . $row['BusinessID'] . "'>" . $row['BusinessName'] . "</a></li>
                    <li style='float:right;'><b>Email:</b> <a href='mailto:" . $row['Email'] . "'>" . ($row['Email'] != '' ? $row['Email'] : 'None') . "</a></li>
                    <li><b>Phone #:</b> " . $row['Phone'] . " ext: " . $row['Ext'] . "</li>
                    <li style='float:right;'>
                        <b>" . ($headerType == "action" ? "Assigned To: " : "Created By: ") . "</b><a href='user.php?UserID=" . $row['UserID'] . "'>" . $row['UserFirstName'] . " " . $row['UserLastName'] . "</a></li>
                    <li><b>Interaction Type:</b> " . $row['InteractionType'] . "</li>
                    <li><div class='notes'> " . $row['Note'] . "</div>";
                        if (is_null($row['actionComplete'])) { // this means that it is either an open action or note
                            if ($row['actionComplete'] == NULL && $headerType == "action") { // this means it is indeed an open action item
                                if ($row['UserID'] == $_SESSION['userID'] || $_SESSION["admin"]) { // this means the user is authorized to edit this action item by either being the user assigned or an admin
                                    $this->printEditBox($headerType, $i, $row['UserID'], $row['BusinessID'], $row['BusinessName'], $row['employeeID'],$row['InteractionType'],$OriginalActionItemID,$actionItemID);
                                }
                            }
                        }
                    print"</li>
                </ul>
        ";

        // this section adds the history of every action item
        if ($headerType == "action") { // if action item print history
            require('includes/mysqli_connect.php');
            // Pull all associated Action Item Data

            $assocActionItemsQuery = pullAssocActionItems($OriginalActionItemID, $NoteID, $originalDate);

            if($assocActionItems = mysqli_query($dbc, $assocActionItemsQuery)) {
                $numHistoryItems = mysqli_num_rows($assocActionItems);
                if(mysqli_num_rows($assocActionItems) == 0) {
                } else {
                    for($j=1; $j <= $numHistoryItems; $j++) { // loop through all history items found
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
    //  IMPORTANT: Displays only open action items.
    function printActionItems(){
        require('includes/mysqli_connect.php');
        // Store Action Items query to variable
        $actionItemsQuery = "";

        if ($this->userID > 0) {
            $actionItemsQuery = pullUserActionItems($this->userID,"UserID");
        } elseif ($this->employeeID > 0) {
            $actionItemsQuery = pullUserActionItems($this->employeeID,"EmployeeID");
        } elseif ($this->businessID > 0) {
            $actionItemsQuery = pullUserActionItems($this->businessID,"BusinessID");
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
                            $this->printItem($i."A", $row, "action");
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
        } elseif ($this->employeeID > 0) {
            $notesQuery = pullUserNotes($this->employeeID,"EmployeeID");
        } elseif ($this->businessID > 0) {
            $notesQuery = pullUserNotes($this->businessID,"BusinessID");
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
                            $this->printItem($i."N", $row, "note");
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
    // This list is necessary from the other two because it prints both items intermingled
    // this gives the user a better idea of the order in which the items happened.
    function printInteractions(){
        require('includes/mysqli_connect.php');
        // Store Action Items query to variable
        $this->alreadyPrintedNotes = [];
        $actionItemsQuery = "";
        $name = "";

        if ($this->userID > 0) {
            $actionItemsQuery = pullInteractions($this->userID,"UserID");
            $name = "User";
        } elseif ($this->employeeID > 0) {  // Pull employee first because both employee and business will be populated
            $actionItemsQuery = pullInteractions($this->employeeID,"EmployeeID");
            $name = "Employee";
        } elseif ($this->businessID > 0) {
            $actionItemsQuery = pullInteractions($this->businessID,"BusinessID");
            $name = "Business";
        }

        //print $actionItemsQuery;
        if ($name != "") { // checks to make sure at least one ID is set

            // Prints 'add new interaction' box, but only on business or employee pages.
            // Cannot do from dashboard or user because you do not know which business to assign to
            // and there will be too many to populate a dropdown
            if ($this->employeeID != "" || $this->businessID != ""){
                $this->printEditBox("note",0,"",$this->businessID,$this->businessName,$this->employeeID,"",0,0,0);
            }
            // Run Action Items query
            if ($userActionItems = mysqli_query($dbc, $actionItemsQuery)) {
                if (mysqli_num_rows($userActionItems) == 0) { // If no action items are present, print statement
                    print '<p style="color:red">No Interactions for this ' . $name . '</p>';
                } else {
                    $numberOfActionItems = mysqli_num_rows($userActionItems);
                    //print "<h4 style='padding-left: 25px;'>Action Items: 1-<b>$numberOfActionItems</b></h4>";

                    for ($i = 1; $i <= $numberOfActionItems; $i++) {
                        if ($row = mysqli_fetch_array($userActionItems)) {
                            if (!in_array($row['NoteID'], $this->alreadyPrintedNotes)) { // if not already printed
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
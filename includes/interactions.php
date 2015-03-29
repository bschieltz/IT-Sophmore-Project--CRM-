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

    function getUserID(){return $this->userID;}
    function getBusinessID(){return $this->businessID;}
    function getEmployeeID(){return $this->employeeID;}
    function setUserID($userID){$this->userID = $userID;}
    function setBusinessID($businessID){$this->businessID = $businessID;}
    function setEmployeeID($employeeID){$this->employeeID = $employeeID;}

    /****************************************************************************************/
    // Prints individual note / action item record
    private function printItem($i,$row,$headerType){
        $actionDateTime = strtotime($row['NoteCreated']);
        $actionDateTime = date("m/d/Y h:i a", $actionDateTime);
        $actionCompete = "";
        if (!is_null($row['actionComplete'])) {
            $actionCompete = " complete";
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
                    <li style='float:right;'><b>Employee:</b> <a href='employee.php?EmployeeID=" . $row['employeeID'] . "'>" . $row['FirstName'] . " " . $row['LastName'] . "</a></li>
                    <li><b>Business: </b><a href='business.php?BusinessID=" . $row['BusinessID'] . "'>" . $row['BusinessName'] . "</a></li>
                    <li style='float:right;'><b>Email:</b> <a href='mailto:" . $row['Email'] . "'>" . $row['Email'] . "</a></li>
                    <li><b>Phone #:</b> " . $row['Phone'] . " ext: " . $row['Ext'] . "</li>
                    <li><b>Interaction Type:</b> " . $row['InteractionType'] . "</li>
                    <li><div class='notes'> " . $row['Note'] . "</div>";
                        if (!is_null($row['actionComplete'])) {
                            print"<h4 style='width: 75%; margin-left: auto; margin-right: auto; text-align: center;'>
                                <!-- Need to add Links -->
                                <a href=''>Add Note</a> |
                                <a href=''>Forward</a> |
                                <a href=''>Mark Complete</a>
                            </h4>";
                        }
                    print"</li>
                </ul>
        ";

        if ($headerType == "action") { // if action item print history
            require('includes/mysqli_connect.php');
            // Pull all associated Action Item Data
            $OriginalActionItemID = $row['OriginalActionItemID'];
            $NoteID = $row['NoteID'];
            $originalDate = $row['ActionItemCreated'];

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
                                    <li><a href='#' name='ExpandAIH$i$j' class='AIHClass'>History Item $j</a></li>
                                </ul>
                                <ul name='toExpandAIH$i$j' class='DashAI displayOff DashAIH toExpandAIaction$i'>
                                    <li><b>User:</b> $pUserName &nbsp&nbsp&nbsp <b>Date:</b> $AIDateTime</li>
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
            $actionItemsQuery = pullUserActionItems($this->userID);
        } elseif ($this->businessID > 0) {

        } elseif ($this->employeeID > 0) {

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
        if ($this->userID > 0) {
            $notesQuery = pullUserNotes($this->userID);
        } elseif ($this->businessID > 0) {

        } elseif ($this->employeeID > 0) {

        }

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
    function printInteractions(){}
}
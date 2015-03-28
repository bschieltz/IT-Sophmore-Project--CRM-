<?php
/**
 * Created by Brian.
 * User: admin
 * Date: 3/24/2015
 * Time: 10:25 PM
 */

class Interactions {
    private $userID = 0;
    private $businessID = 0;
    private $employeeID = 0;

    function getUserID(){return $this->userID;}
    function getBusinessID(){return $this->businessID;}
    function getEmployeeID(){return $this->employeeID;}
    function setUserID($userID){$this->userID = $userID;}
    function setBusinessID($businessID){$this->businessID = $businessID;}
    function setEmployeeID($employeeID){$this->employeeID = $employeeID;}

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
    }

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
                    if ($row = mysqli_fetch_array($notes)) {
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

    function printInteractions(){}
}
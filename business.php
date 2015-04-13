<?php
/**
 * Created By: Brian
 * Date: 1/31/2015
 * Time: 10:58 PM
 * Shows Individual Employees
 *
 * Default state of page is the business search
 * To call a specific business use get: ?BusinessID=1
 * To skip straight to creating a business use get: ?CreateBusiness=True

 * All information is filled out on page load.  It is just hidden data until needed
 * Which information is displayed is decided by class tags.
 * Classes that exist on the page are formTag, searchTag, listTag, infoTag

 * Send search to page using ?Search=
 * mandatory code for a search box would look like:
        <form action="business.php">
            <input type="search" name="Search" placeholder="Search for a Business" />
            <input type="submit" value="Search" />
        </form>
 * can be modified as necessary with id / class tags
 */
require 'templates/header.html';
require('includes/mysqli_connect.php');
ini_set('display_errors',1);  error_reporting(E_ALL);
?>

<?php
    $businessID = 0;
    $businessName = "";
    $primaryContact = "";
    $primaryPhoneNumber = "";
    $notes = "";
    $business = [];
    $employees = [];
    $ucStaff = [];
    $street1 = "";
    $street2 = "";
    $zip_code = "";
    $city = "";
    $statePrefix = "";
    $submitSuccessful = true;  // defaults to true.  only turns false if database update fails for validation reasons.

    if (!empty($_GET['Submit'])) { //if ?Submit=True in URL then from was submitted, update or add ensues
        $businessID = $_GET['BusinessID'];
        $businessName = $_GET['BusinessName'];
        $primaryContact = $_GET['PrimaryContact'];
        $primaryPhoneNumber = $_GET['PrimaryPhoneNumber'];
        $notes = $_GET['Notes'];
        $street1 = $_GET['Street1'];
        $street2 = $_GET['Street2'];
        $zip_code = $_GET['zip_code'];
        $city = $_GET['city'];
        $statePrefix = $_GET['StatePrefix'];

        //send all data to function for update. If businessID == 0 then it adds to database, otherwise it updates
        //first item in array is true/false for successful entry, second is businessID
        //form validation code is inside the function
        $submitResult = pushBusiness($businessID,$businessName,$primaryContact,$primaryPhoneNumber,$notes,$street1,$street2,$zip_code);
        $submitSuccessful = $submitResult[0];
        if ($submitSuccessful){$businessID = $submitResult[1];}
    }

    // BusinessID must already be set or exist in the url get
    // by requiring $submitSuccessful a failed update/add will cause the form to get populated with the submitted information for correction
    if ((!empty($_GET['BusinessID']) or $businessID > 0) and $submitSuccessful) {
        if ($businessID == 0) { // if businessID is not set grab from URL
            $businessID = $_GET['BusinessID'];
        }

        //query to get business information
        $businessQuery = "SELECT BusinessName, PrimaryContact, `PrimaryPhone#`, Notes
                  FROM tbusiness WHERE BusinessID = $businessID";
        if ($business = mysqli_query($dbc, $businessQuery)) {
            $row = mysqli_fetch_array($business);
            $businessName = $row['BusinessName'];
            $primaryContact = $row['PrimaryContact'];
            $primaryPhoneNumber = $row['PrimaryPhone#'];
            $notes = $row['Notes'];
        }

        //query to get business address
        $addressQuery = "SELECT Street1, Street2, zip_code, city, StatePrefix
            FROM taddress INNER JOIN tzips on taddress.ZipsID = tzips.ZipsID
                          INNER JOIN tstate on tzips.StateID = tstate.StateID
            WHERE taddress.BusinessID = $businessID
            AND taddress.EmployeeID IS NULL";
        if ($address = mysqli_query($dbc, $addressQuery)) {
            $row = mysqli_fetch_array($address);
            $street1 = $row['Street1'];
            $street2 = $row['Street2'];
            $zip_code = $row['zip_code'];
            $city = $row['city'];
            $statePrefix = $row['StatePrefix'];
        }

        //Query to get employees of business
        $employeesQuery = "SELECT EmployeeID, FirstName, LastName
            FROM temployee WHERE BusinessID = $businessID and Active = 1";
        $employees = mysqli_query($dbc, $employeesQuery);

        //Query to get top 5 UC employees that contact business the most, in order
        $ucStaffQuery = "SELECT tnote.UserID, COUNT(*) as userCount, tuser.FirstName, tuser.LastName
            FROM tnote INNER JOIN tuser ON tnote.UserID = tuser.UserID
            WHERE BusinessID = $businessID
            GROUP BY UserID
            ORDER BY userCount Desc
            LIMIT 5";
        $ucStaff = mysqli_query($dbc, $ucStaffQuery);

    } elseif (!empty($_GET['CreateBusiness']) or !$submitSuccessful) {

    } elseif (!empty($_GET['Search'])) {

    }

?>
<div id="businessPage">

    <!-- Add button -->
    <form class="searchTag listTag infoTag"  action="business.php">
        <input type="hidden" name="CreateBusiness" value="True"/>
        <input id="addBusinessButton" type="submit" value="Add New Business" />
    </form>

    <?php if ($businessID > 0) { //if we load a business, add create employee button
        print'<form class="infoTag" action="employee.php">
                <input type="hidden" name="CreateEmployee" value="True"/>
                <input type="hidden" name="BusinessID" value=" '. $businessID . '"/>
                <input class="searchTag listTag infoTag" id="addEmployeeButton" type="submit" value="Add New Employee" />
              </form>';
        }
    ?>
    <!-- Search Box -->
    <form class="searchTag listTag" action="business.php">
        <input id="searchInput" type="search" name="Search" placeholder="Search for a Business" />
        <input id="searchButton" type="submit" value="Search" />
    </form>

    <!-- Search Results -->
    <div class="listTag displayOff">
        <ul>
            <?php if (!empty($_GET['Search'])) {displayBusinessList();} ?>
        </ul>
    </div>

    <!-- Business Info -->
    <div class="infoTag displayOff">
        <ul>
            <li>Business Name: <?= $businessName ?></li>
            <li>Primary Contact: <?= $primaryContact ?></li>
            <li>Phone Number: <?= $primaryPhoneNumber ?></li>
            <li>Address: <?= $street1 . " " . $street2 ?></li>
            <li>City/State/Zip: <?= $city . ", " . $statePrefix . " " . $zip_code ?></li>
            <li>Notes: <?= $notes ?></li>
        </ul>
        <input id="editButton" type="submit" value="Edit" />

        <!-- List of employees that work for the company -->
        <dl>
            <dt>Employees:</dt>
            <?php
                if($employees){
                    for($i=0; $i <= mysqli_num_rows($employees); $i++) {
                        if($row = mysqli_fetch_array($employees)) {
                            print '<dd><a href="employee.php?EmployeeID='. $row['EmployeeID'] . '">' . $row['FirstName'] . " " . $row['LastName'] . '</a></dd>';
                        }
                    }
                }
            ?>
        </dl>

        <!-- list of top 5 UC employees that contact business in order of most contact -->
        <dl>
            <dt>Most often in contact with UC Staff:</dt>
            <?php
            if($ucStaff){
                for($i=0; $i <= mysqli_num_rows($ucStaff); $i++) {
                    if($row = mysqli_fetch_array($ucStaff)) {
                        print '<dd><a href="user.php?UserID='. $row['UserID'] . '">' . $row['FirstName'] . " " . $row['LastName'] . '</a></dd>';
                    }
                }
            }
            ?>
        </dl>
    </div>

    <!-- Edit / Add form.  If the variables have data the fields get filled out -->
    <!-- The submit receiving function knows if it is an add or edit based on the existence of a business id -->
    <form class="formTag displayOff">
        <input type="hidden" name="Submit" value="True"/> <!-- hidden field to pass submit value and trigger function -->
        <input type="hidden" name="BusinessID" value="<?= $businessID ?>"/> <!-- hidden field to pass business id -->
        Business Name: <input type="text" name="BusinessName" size="20" value="<?= $businessName ?>" placeholder="Starbucks"/><br />
        Primary Contact: <input type="text" name="PrimaryContact" size="20" value="<?= $primaryContact ?>" placeholder="Bill Jones"/><br />
        Phone Number: <input type="text" name="PrimaryPhoneNumber" size="20" value="<?= $primaryPhoneNumber ?>" placeholder="513-987-6543"/><br />
        Address: <input type="text" name="Street1" size="20" value="<?= $street1 ?>" placeholder="123 Main St"/><br />
        <input type="text" name="Street2" size="20" value="<?= $street2 ?>" placeholder="Suite 345"/><br />
        City/State/Zip: <input type="text" name="city" size="15" value="<?= $city ?>" placeholder="Cincinnati"/>
        <input type="text" name="StatePrefix" size="1" value="<?= $statePrefix ?>" placeholder="OH"/>
        <input type="text" name="zip_code" size="2" value="<?= $zip_code ?>" placeholder="45255"/><br />
        Notes:<br /><textarea name="Notes" rows="4" cols="50"><?= $notes ?></textarea><br />

        <input id="cancelButton" type="submit" value="Cancel" />
        <input id="submitButton" type="submit" value="Submit" />
    </form>

    <br /><br /><br />

    <?php // decide which layout to show
    // javascript adds a css class to hide parts of the page we want hidden.
    // this makes it possible to change what's displayed on the page without reloading the page.
    // a reload should only occur when and edit/add is submitted, add button is clicked, or a search is submitted
    if ((!empty($_GET['BusinessID']) or $businessID > 0) and $submitSuccessful) {
        $actionItems = new Interactions();
        $actionItems->setBusinessID($businessID);
        $actionItems->submitInteraction();
        $actionItems->printInteractions();
        print'<script type="text/javascript">showTag(".infoTag")</script>';
    } elseif (!empty($_GET['CreateBusiness']) or !$submitSuccessful) {
        print'<script type="text/javascript">showTag(".formTag")</script>';
    } elseif (!empty($_GET['Search'])) {
        print'<script type="text/javascript">showTag(".listTag")</script>';
    }
    ?>
</div> <!-- ends business page div -->
<?php
require 'templates/footer.html';
?>

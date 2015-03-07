<?php
/**
 * Created By: Brian
 * Date: 1/31/2015
 * Time: 10:58 PM
 * Shows Individual Employees
 */
require 'templates/header.html';
ini_set('display_errors',1);  error_reporting(E_ALL);
?>

<?php
    include('includes/mysqli_connect.php');
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

    if (!empty($_GET['Submit'])) {
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
        $submitResult = pushBusiness($businessID,$businessName,$primaryContact,$primaryPhoneNumber,$notes,$street1,$street2,$zip_code);
        $submitSuccessful = $submitResult[0];
        if ($submitSuccessful){$businessID = $submitResult[1];}
    }

    if ((!empty($_GET['BusinessID']) or $businessID > 0) and $submitSuccessful) {
        if ($businessID == 0) {
            $businessID = $_GET['BusinessID'];
        }
        $businessQuery = "SELECT BusinessName, PrimaryContact, `PrimaryPhone#`, Notes
                  FROM tbusiness WHERE BusinessID = $businessID";
        if ($business = mysqli_query($dbc, $businessQuery)) {
            $row = mysqli_fetch_array($business);
            $businessName = $row['BusinessName'];
            $primaryContact = $row['PrimaryContact'];
            $primaryPhoneNumber = $row['PrimaryPhone#'];
            $notes = $row['Notes'];
        }

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

        $employeesQuery = "SELECT EmployeeID, FirstName, LastName
            FROM temployee WHERE BusinessID = $businessID and Active = 1";
        $employees = mysqli_query($dbc, $employeesQuery);

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
    <input class="searchTag listTag infoTag" id="addButton" type="submit" value="Add New Business" />
    <?php if ($businessID > 0) {
        print'<form class="infoTag" action="employee.php">
                <input type="hidden" name="CreateEmployee" value="True"/>
                <input type="hidden" name="BusinessID" value=" '. $businessID . '"/>
                <input class="searchTag listTag infoTag" id="addEmployeeButton" type="submit" value="Add New Employee" />
              </form>';
        }
    ?>
    <form class="searchTag" action="business.php">
        <input type="search" name="Search" placeholder="Search for a Business" />
        <input id="searchButton" type="submit" value="Search" />
    </form>

    <div class="listTag displayOff">
        <ul>
            <?php if (!empty($_GET['Search'])) {displayBusinessList();} ?>
        </ul>
    </div>

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

        <dl>
            <dt>Employees:</dt>
            <?php
                if($employees){
                    for($i=0; $i <= mysqli_num_rows($employees); $i++) {
                        if($row = mysqli_fetch_array($employees)) {
                            print '<dd><a href="employee.php?employeeID='. $row['EmployeeID'] . '">' . $row['FirstName'] . " " . $row['LastName'] . '</a></dd>';
                        }
                    }
                }
            ?>
        </dl>

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

    <form class="formTag displayOff">
        <input type="hidden" name="Submit" value="True"/>
        <input type="hidden" name="BusinessID" value="<?= $businessID ?>"/>
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
    if ((!empty($_GET['BusinessID']) or $businessID > 0) and $submitSuccessful) {
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

<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 1/31/2015
 * Time: 1:55 PM
 * Landing page for all users.  If not logged in, show login info.  If logged in, it should be a dashboard of sorts
 */

require 'templates/header.html';
ini_set('display_errors',1);  error_reporting(E_ALL);
?>

<?php
    include('includes/mysqli_connect.php');
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


    if (!empty($_GET['BusinessID'])) {
        $businessID = $_GET['BusinessID'];
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

    } elseif (!empty($_GET['CreateBusiness'])) {

    } elseif (!empty($_GET['Search'])) {

    }

?>
<div id="businessPage">
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
            <li><?= $city . ", " . $statePrefix . " " . $zip_code ?></li>
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
        Business Name: <input type="text" name="BusinessName" size="20" value="<?= $businessName ?>" placeholder="Starbucks"/><br />
        Primary Contact: <input type="text" name="PrimaryContact" size="20" value="<?= $primaryContact ?>" placeholder="Bill Jones"/><br />
        Phone Number: <input type="text" name="PhoneNumber" size="20" value="<?= $primaryPhoneNumber ?>" placeholder="513-987-6543"/><br />
        Notes:<br /><textarea rows="4" cols="50"><?= $notes ?></textarea><br />

        <input id="cancelButton" type="submit" value="Cancel" />
    </form>

    <input class="searchTag listTag" id="addButton" type="submit" value="Add New Business" />

    <br /><br /><br />

    <?php
    if (!empty($_GET['BusinessID'])) {
        print'<script type="text/javascript">showTag(".infoTag")</script>';
    } elseif (!empty($_GET['CreateBusiness'])) {
        print'<script type="text/javascript">showTag(".formTag")</script>';
    } elseif (!empty($_GET['Search'])) {
        print'<script type="text/javascript">showTag(".listTag")</script>';
    }
    ?>
</div> <!-- ends business page div -->
<?php
require 'templates/footer.html';
?>

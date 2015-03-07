<?php
/**
 * Created By: Brian
 * Date: 1/31/2015
 * Time: 10:58 PM
 * Shows Individual Employees
 */
require 'templates/header.html';
?>

<?php
include('includes/mysqli_connect.php');
$businessID = 0;
$employeeID = 0;
$active = 1;
$jobTitle = "";
$titleID = 0;
$firstName = "";
$lastName = "";
$phoneNumber = "";
$extension = "";
$email = "";
$personalNote = "";
$businessName = "";
$business = [];
$ucStaff = [];
$titleList = [];
$submitSuccessful = true;  // defaults to true.  only turns false if database update fails for validation reasons.
$titleList = pullTitles();

if (!empty($_GET['Submit'])) {
    $businessID = $_GET['BusinessID'];
    $employeeID = $_GET['EmployeeID'];
    $active = $_GET['Active'];
    $jobTitle = $_GET['JobTitle'];
    $titleID = $_GET['TitleID'];
    $firstName = $_GET['FirstName'];
    $lastName = $_GET['LastName'];
    $phoneNumber = $_GET['PhoneNumber'];
    $extension = $_GET['Extension'];
    $email = $_GET['Email'];
    $personalNote = $_GET['PersonalNote'];
    $businessName = $_GET['BusinessName'];
    $submitResult = pushEmployee($businessID,$employeeID,$jobTitle,$titleID,$firstName,$lastName,$phoneNumber,$extension,$email,$personalNote);
    $submitSuccessful = $submitResult[0];
    if ($submitSuccessful){$employeeID = $submitResult[1];}

    $titleQuery = "SELECT Title
                  FROM ttitle WHERE TitleID = $titleID";
    if ($titleResult = mysqli_query($dbc, $titleQuery)) {
        $row = mysqli_fetch_array($titleResult);
        $title = $row['Title'];
    }
}

if ((!empty($_GET['EmployeeID']) or $employeeID > 0) and $submitSuccessful) {
    if ($employeeID == 0) {
        $employeeID = $_GET['EmployeeID'];
    }
    $employeeQuery = "SELECT `BusinessID`, `Active`, `JobTitle`, `TitleID`, `FirstName`, `LastName`,
                             `PhoneNumber`, `Extension`, `Email`, `PersonalNote` FROM `temployee`
                      WHERE EmployeeID = $employeeID";
    if ($employee = mysqli_query($dbc, $employeeQuery)) {
        $row = mysqli_fetch_array($employee);
        $businessID = $row['BusinessID'];
        $active = $row['Active'];
        $jobTitle = $row['JobTitle'];
        $titleID = $row['TitleID'];
        $firstName = $row['FirstName'];
        $lastName = $row['LastName'];
        $phoneNumber = $row['PhoneNumber'];
        $extension = $row['Extension'];
        $email = $row['Email'];
        $personalNote = $row['PersonalNote'];
    }

    $businessQuery = "SELECT BusinessName, PrimaryContact, `PrimaryPhone#`, Notes
                  FROM tbusiness WHERE BusinessID = $businessID";
    if ($business = mysqli_query($dbc, $businessQuery)) {
        $row = mysqli_fetch_array($business);
        $businessName = $row['BusinessName'];
    }

    $titleQuery = "SELECT Title
                  FROM ttitle WHERE TitleID = $titleID";
    if ($titleResult = mysqli_query($dbc, $titleQuery)) {
        $row = mysqli_fetch_array($titleResult);
        $title = $row['Title'];
    }

    $ucStaffQuery = "SELECT tnote.UserID, COUNT(*) as userCount, tuser.FirstName, tuser.LastName
            FROM tnote INNER JOIN tuser ON tnote.UserID = tuser.UserID
            WHERE EmployeeID = $employeeID
            GROUP BY UserID
            ORDER BY userCount Desc
            LIMIT 5";
    $ucStaff = mysqli_query($dbc, $ucStaffQuery);

} elseif (!empty($_GET['CreateEmployee']) or !$submitSuccessful) {
    $businessID = $_GET['BusinessID'];
} elseif (!empty($_GET['Search'])) {

}

?>
<div id="businessPage">
    <form class="searchTag" action="employee.php">
        <input type="search" name="Search" placeholder="Search for an employee" />
        <input id="searchButton" type="submit" value="Search" />
    </form>

    <div class="listTag displayOff">
        <ul>
            <?php if (!empty($_GET['Search'])) {displayEmployeeList();} ?>
        </ul>
    </div>

    <div class="infoTag displayOff">
        <ul>
            <li>Status: <?= ($active == 1 ? "Active" : "Inactive") ?></li>
            <li>Job Title: <?= $jobTitle ?></li>
            <li>Title: <?= $title ?></li>
            <li>First Name: <?= $firstName ?></li>
            <li>Last Name: <?= $lastName ?></li>
            <li>Phone Number: <?= $phoneNumber ?></li>
            <li>Ext: <?= $extension ?></li>
            <li>Email: <?= $email ?></li>
            <li>Personal Note: <?= $personalNote ?></li>
            <li>Works for: <a href="business.php?BusinessID=<?= $businessID ?>"><?= $businessName ?></a></li>
        </ul>
        <input id="editButton" type="submit" value="Edit" />

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
        <input type="hidden" name="EmployeeID" value="<?= $employeeID ?>"/>
        Job Title: <input type="text" name="JobTitle" size="20" value="<?= $jobTitle ?>" placeholder=""/><br />
        Title: <select name="TitleID">
            <?php
                for ($i=0; $i < sizeof($titleList); $i++) {
                    print'<option value="' . $titleList[$i][1] . '"' . ($titleList[$i][0]==$title ? ' Selected' : '') . '>' . $titleList[$i][0] . '</option>' . "\r\n";
                }
            ?>
        </select>
        First Name: <input type="text" name="FirstName" size="20" value="<?= $firstName ?>" placeholder=""/><br />
        Last Name: <input type="text" name="LastName" size="20" value="<?= $lastName ?>" placeholder=""/><br />
        Phone Number: <input type="text" name="PhoneNumber" size="20" value="<?= $phoneNumber ?>" placeholder=""/><br />
        Ext: <input type="text" name="Extension" size="20" value="<?= $extension ?>" placeholder=""/><br />
        Email: <input type="text" name="Email" size="20" value="<?= $email ?>" placeholder=""/><br />
        Note: <input type="text" name="PersonalNote" size="20" value="<?= $personalNote ?>" placeholder=""/><br />
        <input id="cancelButton" type="submit" value="Cancel" />
        <input id="submitButton" type="submit" value="Submit" />
    </form>

    <br /><br /><br />

    <?php // decide which layout to show
    if ((!empty($_GET['EmployeeID']) or $employeeID > 0) and $submitSuccessful) {
        print'<script type="text/javascript">showTag(".infoTag")</script>';
    } elseif (!empty($_GET['CreateEmployee']) or !$submitSuccessful) {
        print'<script type="text/javascript">showTag(".formTag")</script>';
    } elseif (!empty($_GET['Search'])) {
        print'<script type="text/javascript">showTag(".listTag")</script>';
    }
    ?>
</div> <!-- ends business page div -->
<?php
require 'templates/footer.html';
?>

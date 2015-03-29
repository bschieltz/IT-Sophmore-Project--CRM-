<?php
/**
 * Created By: Brian
 * Date: 1/31/2015
 * Time: 10:58 PM
 * Shows Individual Employees
 *
 * Default state of page is the employee search
 * To call a specific employee use get: ?EmployeeID=1
 * To skip straight to creating an employee use get (requires BusinessID): ?CreateEmployee=True&BusinessID=1

 * All information is filled out on page load.  It is just hidden data until needed
 * Which information is displayed is decided by class tags.
 * Classes that exist on the page are formTag, searchTag, listTag, infoTag

 * Send search to page using ?Search=
 * mandatory code for a search box would look like:
        <form action="employee.php">
            <input type="search" name="Search" placeholder="Search for a Employee" />
            <input type="submit" value="Search" />
        </form>
 * can be modified as necessary with id / class tags
 */
require 'templates/header.html';
require('includes/mysqli_connect.php');
?>

<?php
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

    //send all data to function for update. If employeeID == 0 then it adds to database, otherwise it updates
    //first item in returned array is true/false for successful entry, second is businessID
    //form validation code is inside the function
    $submitResult = pushEmployee($businessID,$employeeID,$jobTitle,$titleID,$firstName,$lastName,$phoneNumber,$extension,$email,$personalNote);
    $submitSuccessful = $submitResult[0];
    if ($submitSuccessful){$employeeID = $submitResult[1];} //if successful assign employeeID

    // Get employee title
    $title = getTitle($titleID);

}

if (($_GET['ChangeActive'] == 0) || ($_GET['ChangeActive'] == 1)) {
    $active = $_GET['ChangeActive'];
    $employeeID = $_GET['EmployeeID'];
    flipActive($active,"employee",$employeeID);
}

// EmployeeID must already be set or exist in the url get
// by requiring $submitSuccessful a failed update/add will cause the form to get populated with the submitted information for correction
if ((!empty($_GET['EmployeeID']) or $employeeID > 0) and $submitSuccessful) {
    if ($employeeID == 0) {
        $employeeID = $_GET['EmployeeID'];
    }
    $employeeQuery = "SELECT `BusinessID`, `Active`, `JobTitle`, `TitleID`, `FirstName`, `LastName`,
                             `PhoneNumber`, `Extension`, `Email`, `PersonalNote` FROM `temployee`
                      WHERE EmployeeID = $employeeID";
    if ($employee = mysqli_query($dbc, $employeeQuery)) { // grab employee info from table
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

    // Grab name of business employee works for
    $businessQuery = "SELECT BusinessName, PrimaryContact, `PrimaryPhone#`, Notes
                  FROM tbusiness WHERE BusinessID = $businessID";
    if ($business = mysqli_query($dbc, $businessQuery)) {
        $row = mysqli_fetch_array($business);
        $businessName = $row['BusinessName'];
    }

    // get title, ie mr, mrs etc
    $title = getTitle($titleID);

    // get 5 most contacted UC employees
    $ucStaffQuery = "SELECT tnote.UserID, COUNT(*) as userCount, tuser.FirstName, tuser.LastName
            FROM tnote INNER JOIN tuser ON tnote.UserID = tuser.UserID
            WHERE EmployeeID = $employeeID
            GROUP BY UserID
            ORDER BY userCount Desc
            LIMIT 5";
    $ucStaff = mysqli_query($dbc, $ucStaffQuery);

} elseif (!empty($_GET['CreateEmployee']) or !$submitSuccessful) { // if create called or submission fails get business ID from url
    // business id needed to create employee and link to their employer
    $businessID = $_GET['BusinessID'];
} elseif (!empty($_GET['Search'])) {

}

?>
<div id="employeePage">

    <!-- Employee search -->
    <form class="searchTag listTag" action="employee.php">
        <input type="search" name="Search" placeholder="Search for an employee" />
        <input id="searchButton" type="submit" value="Search" />
    </form>

    <!-- Search results -->
    <div class="listTag displayOff">
        <ul>
            <?php if (!empty($_GET['Search'])) {displayEmployeeList();} ?>
        </ul>
    </div>

    <!-- Active / Inactive Button -->
    <form class="formTag displayOff" action="employee.php">
        <input type="hidden" name="ChangeActive" value="<?= $active ?>"/>
        <input type="hidden" name="EmployeeID" value="<?= $employeeID ?>"/>
        <input class="formTag" id="changeActive" type="submit" value=<?= ($active ? print'"Suspend Employee"' : print'"Activate Employee"') ?>/>
    </form>

    <!-- Employee Information -->
    <div class="infoTag displayOff">
        <ul>
            <li>Status: <?= ($active ? "Active" : "Inactive") ?></li>
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

        <!-- most contacted UC staff based on number of notes -->
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

    <!-- Form for adding/editing employees. -->
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
    // javascript adds a css class to hide parts of the page we want hidden.
    // this makes it possible to change what's displayed on the page without reloading the page.
    // a reload should only occur when and edit/add is submitted, add button is clicked, or a search is submitted
    if ((!empty($_GET['EmployeeID']) or $employeeID > 0) and $submitSuccessful) {
        $actionItems = new Interactions();
        $actionItems->setEmployeeID($employeeID);
        $actionItems->printActionItems();
        $actionItems->printNotes();
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

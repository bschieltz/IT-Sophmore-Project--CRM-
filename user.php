<?php
/**
 * Created By: Brian
 * Date: 1/31/2015
 * Time: 10:58 PM
 * Shows Individual Users
 *
 * Default state of page is the user search
 * To call a specific user use get: ?UserID=1
 * To skip straight to creating an user use get (requires BusinessID): ?CreateUser=True

 * All information is filled out on page load.  It is just hidden data until needed
 * Which information is displayed is decided by class tags.
 * Classes that exist on the page are formTag, searchTag, listTag, infoTag

 * Send search to page using ?Search=
 * mandatory code for a search box would look like:
    <form action="user.php">
        <input type="search" name="Search" placeholder="Search for a User" />
        <input type="submit" value="Search" />
    </form>
 * can be modified as necessary with id / class tags
 */
require 'templates/header.html';
require('includes/mysqli_connect.php');
?>

<?php
$userID = 0;
$titleID = 0;
$title = "";
$firstName = "";
$lastName = "";
$email = "";
$admin = 0;
$active = 0;
$phoneNumber = "";
$interactionType = "";
$interactionTypeID = 0;
$password1 = "";
$password2 = "";
$submitSuccessful = true;  // defaults to true.  only turns false if database update fails for validation reasons.
$titleList = pullTitles();
$interactionList = pullInteractionTypes();
$businesses = [];
$employees = [];

if (isset($_POST['Submit'])) {
    $userID = $_POST['UserID'];
    $titleID = $_POST['TitleID'];
    $firstName = $_POST['FirstName'];
    $lastName = $_POST['LastName'];
    $email = $_POST['Email'];
    $admin = (isset($_POST['Admin']) ? 1 : 0);
    $phoneNumber = $_POST['PhoneNumber'];
    $interactionTypeID = $_POST['InteractionTypeID'];
    $password1 = (isset($_POST['Password1']) ? $_POST['Password1'] : "");
    $password2 = (isset($_POST['Password2']) ? $_POST['Password2'] : "");

    //send all data to function for update. If UserID == 0 then it adds to database, otherwise it updates
    //first item in returned array is true/false for successful entry, second is businessID
    //form validation code is inside the function
    $submitResult = pushUser($userID,$titleID,$firstName,$lastName,$email,$admin,$phoneNumber,$interactionTypeID,$password1,$password2);
    $submitSuccessful = $submitResult[0];
    if ($submitSuccessful){$userID = $submitResult[1];} //if successful assign userID

    // Get user title
    $title = getTitle($titleID);

    // Get user interaction type
    $interactionType = getInteractionType($interactionTypeID);
}

if (($_POST['ChangeActive'] == 0) || ($_POST['ChangeActive'] == 1)) {
    $active = $_POST['ChangeActive'];
    $userID = $_POST['UserID'];
    flipActive($active,"user",$userID);
}

// UserID must already be set or exist in the url get
// by requiring $submitSuccessful a failed update/add will cause the form to get populated with the submitted information for correction
if ((isset($_GET['UserID']) or $userID > 0) and $submitSuccessful) {
    if ($userID == 0) {
        if (isset($_GET['AssignedToUserID'])) {
            $userID = $_GET['AssignedToUserID'];
        } else {
            $userID = $_GET['UserID'];
        }
    }
    $userQuery = "SELECT `UserID`, `TitleID`, `FirstName`, `LastName`, `Email`, `Admin`, `Active`,
                 `PhoneNumber`, `InteractionTypeID` FROM `tuser`  WHERE UserID = $userID";
    if ($user = mysqli_query($dbc, $userQuery)) { // grab user info from table
        $row = mysqli_fetch_array($user);
        $titleID = $row['TitleID'];
        $firstName = $row['FirstName'];
        $lastName = $row['LastName'];
        $email = $row['Email'];
        $admin = $row['Admin'];
        $active = $row['Active'];
        $phoneNumber = $row['PhoneNumber'];
        $interactionTypeID = $row['InteractionTypeID'];
    }

    // get title, ie mr, mrs etc
    $title = getTitle($titleID);

    // Get user interaction type
    $interactionType = getInteractionType($interactionTypeID);

    //query to get top 5 businesses contacted
    $businessesQuery = "SELECT tnote.BusinessID, COUNT(*) as businessCount, tbusiness.BusinessName
            FROM tnote INNER JOIN tbusiness ON tnote.BusinessID = tbusiness.BusinessID
            WHERE UserID = $userID
            GROUP BY BusinessID
            ORDER BY businessCount Desc
            LIMIT 5";
    $businesses = mysqli_query($dbc, $businessesQuery);

    //Query to get most contacted employees
    $employeesQuery = "SELECT tnote.EmployeeID, COUNT(*) as employeeCount, temployee.FirstName, temployee.LastName
            FROM tnote INNER JOIN temployee ON tnote.EmployeeID = temployee.EmployeeID
            WHERE UserID = $userID and Active = 1
            GROUP BY EmployeeID
            ORDER BY employeeCount Desc
            LIMIT 5";
    $employees = mysqli_query($dbc, $employeesQuery);


} elseif (!empty($_GET['CreateUser']) or !$submitSuccessful) { // if create called or submission fails get business ID from url

} elseif (!empty($_GET['Search'])) {

}

?>
<div id="userPage">

    <!-- User search -->
    <form class="searchTag listTag" action="user.php">
        <input type="search" name="Search" id='searchInput' placeholder="Search for a User" />
        <input id="searchButton" type="submit" value="Search" />
    </form>

    <!-- Add User Button -->
    <?php ($_SESSION["admin"] ? print'
    <form class="searchTag listTag"  action="user.php">
        <input type="hidden" name="CreateUser" value="True"/>
        <input class="myButton" id="addUserButton" type="submit" value="Add New User" />
    </form>' : ''); ?>

    <!-- Search results -->
    <div class="listTag displayOff">
        <ul>
            <br />
            <h3>Users:</h3>
            <?php if (!empty($_GET['Search'])) {displayUserList();} ?>
        </ul>
    </div>

    <!-- Active / Inactive Button -->
    <?php ($_SESSION["admin"] ? print'
    <form class="formTag displayOff" action="user.php" method="post">
        <input type="hidden" name="ChangeActive" value="' . $active . '"/>
        <input type="hidden" name="UserID" value="' . $userID . '"/>
        <input class="myButton" id="changeActive" type="submit" value="' . ($active ? "Suspend User" : "Activate User") . '"/>
    </form>' : ''); ?>

    <!-- User Information -->
    <div class="infoTag displayOff">
        <!-- Most contacted businesses (which is different from recent businesses) -->
        <div class="mostContacted">
            <dl>
                <dt>Most Contacted Businesses</dt>
                <?php
                if($businesses){
                    for($i=0; $i <= mysqli_num_rows($businesses); $i++) {
                        if($row = mysqli_fetch_array($businesses)) {
                            print '<dd><a href="business.php?BusinessID='. $row['BusinessID'] . '">' . $row['BusinessName'] . '</a></dd>';
                        }
                    }
                }
                ?>
            </dl>

            <!-- Most contacted employees (which is different from recent employees) -->
            <dl>
                <dt>Most Contacted Employees</dt>
                <?php
                if($employees){
                    for($i=0; $i <= mysqli_num_rows($employees); $i++) {
                        if($row = mysqli_fetch_array($employees)) {
                            print '<dd><a href="employee.php?EmployeeID='. $row['EmployeeID'] . '">' . $row['FirstName'] . ' ' . $row['LastName'] . '</a></dd>';
                        }
                    }
                }
                ?>
            </dl>
        </div>

        <ul class="primaryInfo">
            <li><b>Status:</b> <?= ($active ? "Active" : "Inactive") ?></li>
            <li><b>Title:</b> <?= $title ?></li>
            <li><b>First Name:</b> <?= $firstName ?></li>
            <li><b>Last Name:</b> <?= $lastName ?></li>
            <li><b>Phone Number:</b> <?= $phoneNumber ?></li>
            <li><b>Email:</b> <a href="mailto:<?= $email ?>"><?= $email ?></a></li>
            <?php ($_SESSION["admin"] ? print"<li><b>Admin:</b> " . ($admin ? "Yes" : "No") . "</li>" : ""); ?>
            <li><b> Type:</b> <?= $interactionType ?></li>
        </ul>
        <?php ($_SESSION["admin"] || $_SESSION["userID"] == $userID ? print'<input class="myButton" id="editButton" type="submit" value="Edit User Info" />' : '')?>

    </div>

    <!-- Form for adding/editing users. -->
    <form class="primaryInfo formTag displayOff" method="post">
        <input type="hidden" name="Submit" value="True"/>
        <input type="hidden" name="UserID" value="<?= $userID ?>"/>
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
        Email: <input type="text" name="Email" size="20" value="<?= $email ?>" placeholder=""/><br />
        Interaction Type: <select name="InteractionTypeID">
            <?php
            for ($i=0; $i < sizeof($interactionList); $i++) {
                print'<option value="' . $interactionList[$i][1] . '"' . ($interactionList[$i][0]==$interactionType ? ' Selected' : '') . '>' . $interactionList[$i][0] . '</option>' . "\r\n";
            }
            ?>
        </select><br />
        <?php ($_SESSION["admin"] ? print"Admin: <input type='checkbox' name='Admin' value='" . $admin . "' " . ($admin ? ' checked' : '') . "/><br />": ""); ?>
        <?php ($_SESSION["admin"] || $_SESSION["userID"] == $userID ?
            print 'Change Password: <input type="password" name="Password1" value="" /><br />
            Re-Type Password: <input type="password" name="Password2" value="" /><br />' : "");
        ?>
        <input class="myButton" id="submitButton" type="submit" value="Submit" />
        <input class="myButton" id="cancelButton" type="submit" value="Cancel" />
    </form>

    <br />
    <br />
    <br />
    <br />
    <br />

    <?php

    // decide which layout to show
    // javascript adds a css class to hide parts of the page we want hidden.
    // this makes it possible to change what's displayed on the page without reloading the page.
    // a reload should only occur when and edit/add is submitted, add button is clicked, or a search is submitted
    if ((!empty($_GET['UserID']) or $userID > 0) and $submitSuccessful) {
        $actionItems = new Interactions();
        $actionItems->setUserID($userID);
        $actionItems->submitInteraction();
        $actionItems->printInteractions();
        print'<script type="text/javascript">showTag(".infoTag")</script>';
    } elseif (!empty($_GET['CreateUser']) or !$submitSuccessful) {
        print'<script type="text/javascript">showTag(".formTag")</script>';
    } elseif (!empty($_GET['Search'])) {
        print'<script type="text/javascript">showTag(".listTag")</script>';
    }
    ?>
</div> <!-- ends business page div -->
<?php
require 'templates/footer.html';
?>

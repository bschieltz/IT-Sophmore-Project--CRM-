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
?>

<?php
include('includes/mysqli_connect.php');
$userID = 0;
$titleID = 0;
$firstName = "";
$lastName = "";
$email = "";
$admin = 0;
$active = 0;
$phoneNumber = "";
$interactionTypeID = 0;
$submitSuccessful = true;  // defaults to true.  only turns false if database update fails for validation reasons.
$titleList = pullTitles();
$interactionList = pullInteractionTypes();

if (!empty($_GET['Submit'])) {
    $userID = $_GET['UserID'];
    $titleID = $_GET['TitleID'];
    $firstName = $_GET['FirstName'];
    $lastName = $_GET['LastName'];
    $email = $_GET['Email'];
    $admin = $_GET['Admin'];
    $active = $_GET['Active'];
    $phoneNumber = $_GET['PhoneNumber'];
    $interactionTypeID = $_GET['InteractionType'];


    //send all data to function for update. If UserID == 0 then it adds to database, otherwise it updates
    //first item in returned array is true/false for successful entry, second is businessID
    //form validation code is inside the function
    $submitResult = pushUser($userID,$titleID,$firstName,$lastName,$email,$admin,$active,$phoneNumber,$interactionTypeID);
    $submitSuccessful = $submitResult[0];
    if ($submitSuccessful){$userID = $submitResult[1];} //if successful assign userID

    // Get user title
    $title = getTitle($titleID);

    // Get user interaction type
    $interactionType = getInteractionType($interactionTypeID);
}

// UserID must already be set or exist in the url get
// by requiring $submitSuccessful a failed update/add will cause the form to get populated with the submitted information for correction
if ((!empty($_GET['UserID']) or $userID > 0) and $submitSuccessful) {
    if ($userID == 0) {
        $userID = $_GET['UserID'];
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
        $interactionTypeID = $row['InteractionType'];
    }

    // get title, ie mr, mrs etc
    $title = getTitle($titleID);

    // Get user interaction type
    $interactionType = getInteractionType($interactionTypeID);

} elseif (!empty($_GET['CreateUser']) or !$submitSuccessful) { // if create called or submission fails get business ID from url

} elseif (!empty($_GET['Search'])) {

}

?>
<div id="userPage">

    <!-- User search -->
    <form class="searchTag listTag" action="user.php">
        <input type="search" name="Search" placeholder="Search for a User" />
        <input id="searchButton" type="submit" value="Search" />
    </form>

    <!-- Search results -->
    <div class="listTag displayOff">
        <ul>
            <?php if (!empty($_GET['Search'])) {displayUserList();} ?>
        </ul>
    </div>

    <!-- User Information -->
    <div class="infoTag displayOff">
        <ul>
            <li>Status: <?= ($active == 1 ? "Active" : "Inactive") ?></li>
            <li>Title: <?= $title ?></li>
            <li>First Name: <?= $firstName ?></li>
            <li>Last Name: <?= $lastName ?></li>
            <li>Phone Number: <?= $phoneNumber ?></li>
            <li>Email: <?= $email ?></li>
            <li>Admin: <?= $admin ?></li>
            <li>Interaction Type: <?= $interactionType ?></li>;
        </ul>
        <input id="editButton" type="submit" value="Edit" />
    </div>

    <!-- Form for adding/editing users. -->
    <form class="formTag displayOff">
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
        <input id="cancelButton" type="submit" value="Cancel" />
        <input id="submitButton" type="submit" value="Submit" />
    </form>

    <br /><br /><br />

    <?php // decide which layout to show
    // javascript adds a css class to hide parts of the page we want hidden.
    // this makes it possible to change what's displayed on the page without reloading the page.
    // a reload should only occur when and edit/add is submitted, add button is clicked, or a search is submitted
    if ((!empty($_GET['UserID']) or $userID > 0) and $submitSuccessful) {
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

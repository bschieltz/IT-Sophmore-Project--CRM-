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

    if (!empty($_GET['BusinessID'])) {
        $businessID = $_GET['BusinessID'];
        $businessQuery = "SELECT BusinessName, PrimaryContact, `PrimaryPhone#`, Notes
                  FROM tbusiness WHERE BusinessID = $businessID";//pullBusiness($businessID);
        //print $businessQuery;
        $business = mysqli_query($dbc, $businessQuery) or die("Error: ".mysqli_error($dbc));
        //if (mysqli_num_rows($business) > 0) {
            $row = mysqli_fetch_array($business);
            $businessName = $row['BusinessName'];
            $primaryContact = $row['PrimaryContact'];
            $primaryPhoneNumber = $row['PrimaryPhone#'];
            $notes = $row['Notes'];
        //}
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
            <li>Notes: <?= $notes ?></li>
        </ul>
        <input id="editButton" type="submit" value="Edit" />
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

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


<p class="infoTag displayOn">report text</p>
<p class="formTag displayOff">form text</p>
<br /><br /><br />


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
    }
?>
<div class="infoTag">
    <h3>Business Name: <?= $businessName ?></h3>
    <p>Primary Contact: <?= $primaryContact ?></p>
    <p>Phone Number: <?= $primaryPhoneNumber ?></p>
    <p>Notes: <?= $notes ?></p>

    <input id="editButton" type="submit" value="Edit" />
</div>

<form class="formTag displayOff">
    <p>Business Name: <input type="text" name="BusinessName" size="20" value="<?= $businessName ?>" placeholder="Starbucks"/></p>
    <p>Primary Contact: <input type="text" name="PrimaryContact" size="20" value="<?= $primaryContact ?>" placeholder="Bill Jones"/></p>
    <p>Phone Number: <input type="text" name="PhoneNumber" size="20" value="<?= $primaryPhoneNumber ?>" placeholder="513-987-6543"/></p>
    <p>Notes: <input type="text" name="Address" size="20" value="<?= $notes ?>" placeholder="456 Center St."/></p>

    <input id="cancelButton" type="submit" value="Cancel" />
</form>

<br /><br /><br />
<a class="formTag displayOff" href = "business.php?BusinessID=1">Click here to test business with info</a>
<a class="infoTag displayOn" href = "business.php">Click here to test new business page</a>

<?php
if (empty($_GET['BusinessID'])){
    print'<script type="text/javascript">swapDisplay()</script>';
}
?>

<?php
require 'templates/footer.html';
?>

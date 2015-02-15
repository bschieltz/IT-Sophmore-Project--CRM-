<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 1/31/2015
 * Time: 1:55 PM
 * Landing page for all users.  If not logged in, show login info.  If logged in, it should be a dashboard of sorts
 */

require 'header.php';
ini_set('display_errors',1);  error_reporting(E_ALL);
?>


<p class="infoTag displayOn">report text</p>
<p class="formTag displayOff">form text</p>
<br /><br /><br />

<?php
$businessName = "";
$primaryContact = "";
$phoneNumber = "";
$address = "";


if (!empty($_GET['BusinessName'])){
    $businessName = ($_GET['BusinessName']);
    $primaryContact = "Joe Smith"; //insert database info here
    $phoneNumber = "513-123-4567"; //insert database info here
    $address = "123 Main St"; //insert database info here
}
?>

<p class="infoTag displayOn">Business Name: <?= $businessName ?></p>
<p class="infoTag displayOn">Primary contact: <?= $primaryContact ?></p>
<p class="infoTag displayOn">Phone Number: <?= $phoneNumber ?></p>
<p class="infoTag displayOn">Address: <?= $address ?></p>


<br /><br /><br />
<a class="formTag displayOff" href = "business.php?BusinessName=Chipotle">Click here to test business with info</a>
<a class="infoTag displayOn" href = "business.php">Click here to test new business page</a>

<?php
if (empty($_GET['BusinessName'])){
    print'<script type="text/javascript">swapDisplay()</script>';
}
?>

<?php
require 'footer.php';
?>

<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 1/31/2015
 * Time: 1:55 PM
 * Landing page for all users.  If not logged in, show login info.  If logged in, it should be a dashboard of sorts
 */

require 'header.php';
?>


<p class="infoTag displayOn">report text</p>
<p class="formTag displayOff">form text</p>

<?php
if (empty($_GET['business_name'])){
    print'<script type="text/javascript">swapDisplay()</script>';
}

print"<a class=\"formTag\" href = urlencode(\"business.php?BusinessName=Chipotle&PrimayContact=Joe+Smith&PhoneNumber=513-123-4567&Address=123+Main+St\")>Click here<a/> to test business with info<br />";
?>

<?php
require 'footer.php';
?>

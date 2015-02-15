<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 1/31/2015
 * Time: 1:53 PM
 */

require 'header.php';
?>

<p class="infoTag displayOn">report text</p>
<p class="infoTag displayOff">form text</p>

<?php
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (empty($_POST['business_name']))){
} else {
    print'<script type="text/javascript">swapDisplay()</script>';
}
?>


<?php
require 'footer.php';
?>

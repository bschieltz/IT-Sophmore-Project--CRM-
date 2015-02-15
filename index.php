<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 1/31/2015
 * Time: 1:53 PM
 */

require 'header.php';
?>
<?php
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && (empty($_POST['business_name']))){
        print'<script type="text/javascript">swapDisplay(false)</script>';
    } else {
        print'<script type="text/javascript">swapDisplay(true)</script>';
    }
?>


<p class="infoTag displayOn">report text</p>
<p class="infoTag displayOff">form text</p>



<?php
require 'footer.php';
?>

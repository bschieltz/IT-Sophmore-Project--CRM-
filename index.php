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
        $displayForm = false;
    } else {
        $displayForm = true;
    }
?>


<p>test text</p>



<?php
require 'footer.php';
?>

<?php
require 'vendor/autoload.php';
require '../src/KNI.php';

use Jacobtread\KNI\KNI;

$kni = new KNI('demo.school.kiwi');
$notices = $kni->retrieve('01/01/2020');
if ($notices->isSuccess()) {
    var_dump($notices->getNotices());
} else {
    echo $notices->getErrorMessage();
}
<?php
/*
 * redirects.php
 *
 * Using SafeCurl and following redirects with a limit
 */
require '../vendor/autoload.php';

use fin1te\SafeCurl\SafeCurl;
use fin1te\SafeCurl\Options;

try {
    $options = new Options();
    //Follow redirects, but limit to 10
    $options->enableFollowLocation()->setFollowLocationLimit(10);

    $safeCurl = new SafeCurl(curl_init());
    $result = $safeCurl->execute('http://fin1te.net');
} catch (Exception $e) {
    //Handle exception
}

<?php
/*
 * default.php
 *
 * Using SafeCurl with it's default options
 */
require '../vendor/autoload.php';

use fin1te\SafeCurl\SafeCurl;

try {
    $safeCurl = new SafeCurl(curl_init());
    $result = $safeCurl->execute('https://fin1te.net');
} catch (Exception $e) {
    //Handle exception
}

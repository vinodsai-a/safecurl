<?php
/*
 * options.php
 *
 * Using SafeCurl with custom options
 */
require '../vendor/autoload.php';

use fin1te\SafeCurl\Options;
use fin1te\SafeCurl\SafeCurl;

try {
    $options = new Options();
    //Completely clear the whitelist
    $options->setList('whitelist', array());
    //Completely clear the blacklist
    $options->setList('blacklist', array());
    //Set the domain whitelist only
    $options->setList('whitelist', array('google.com', 'youtube.com'), 'domain');

    $safeCurl = new SafeCurl(curl_init());
    $result = $safeCurl->execute('http://www.youtube.com');
} catch (Exception $e) {
    //Handle exception
}

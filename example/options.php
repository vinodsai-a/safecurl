<?php
/*
 * options.php
 *
 * Using SafeCurl with custom options
 */
require '../vendor/autoload.php';

use fin1te\SafeCurl\SafeCurl;
use fin1te\SafeCurl\Options;

try {
    $options = new Options();
    //Completely clear the whitelist
    $options->setList('whitelist', []);
    //Completely clear the blacklist
    $options->setList('blacklist', []);
    //Set the domain whitelist only
    $options->setList('whitelist', ['google.com', 'youtube.com'], 'domain');

    $safeCurl = new SafeCurl(curl_init());
    $result = $safeCurl->execute('http://www.youtube.com');
} catch (Exception $e) {
    //Handle exception
}

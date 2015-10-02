<?php

use fin1te\SafeCurl\SafeCurl;

class SafeCurlTest extends \PHPUnit_Framework_TestCase
{
    public function testFeedIndex()
    {
        $response = SafeCurl::execute('http://www.google.com', curl_init());

        $this->assertNotEmpty($response);
    }
}

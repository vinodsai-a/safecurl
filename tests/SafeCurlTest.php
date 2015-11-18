<?php

use fin1te\SafeCurl\SafeCurl;
use fin1te\SafeCurl\Options;

class SafeCurlTest extends \PHPUnit_Framework_TestCase
{
    public function testFunctionnalGET()
    {
        $handle = curl_init();

        $safeCurl = new SafeCurl($handle);
        $response = $safeCurl->execute('http://www.google.com');

        $this->assertNotEmpty($response);
        $this->assertEquals($handle, $safeCurl->getCurlHandle());
        $this->assertNotContains('HTTP/1.1 302 Found', $response);
    }

    public function testFunctionnalHEAD()
    {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'HEAD');

        $safeCurl = new SafeCurl($handle);
        $response = $safeCurl->execute('https://40.media.tumblr.com/39e917383bf5fe228b82fef850251220/tumblr_nxyw8cjiYx1u7jfjwo1_500.jpg');

        $this->assertNotEmpty($response);
        $this->assertEquals($handle, $safeCurl->getCurlHandle());
        $this->assertNotContains('HTTP/1.1 302 Found', $response);
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception
     * @expectedExceptionMessage SafeCurl expects a valid cURL resource - "NULL" provided.
     */
    public function testBadCurlHandler()
    {
        new SafeCurl(null);
    }

    public function dataForBlockedUrl()
    {
        return array(
            array('http://0.0.0.0:123', 'fin1te\SafeCurl\Exception\InvalidURLException\InvalidPortException', 'Provided port "123" doesn\'t match whitelisted values: 80, 443, 8080'),
            array('http://127.0.0.1/server-status', 'fin1te\SafeCurl\Exception\InvalidURLException\InvalidIPException', 'Provided host "127.0.0.1" resolves to "127.0.0.1", which matches a blacklisted value: 127.0.0.0/8'),
            array('file:///etc/passwd', 'fin1te\SafeCurl\Exception\InvalidURLException', 'Provided URL "file:///etc/passwd" doesn\'t contain a hostname'),
            array('ssh://localhost', 'fin1te\SafeCurl\Exception\InvalidURLException\InvalidSchemeException', 'Provided scheme "ssh" doesn\'t match whitelisted values: http, https'),
            array('gopher://localhost', 'fin1te\SafeCurl\Exception\InvalidURLException\InvalidSchemeException', 'Provided scheme "gopher" doesn\'t match whitelisted values: http, https'),
            array('telnet://localhost:25', 'fin1te\SafeCurl\Exception\InvalidURLException\InvalidSchemeException', 'Provided scheme "telnet" doesn\'t match whitelisted values: http, https'),
            array('http://169.254.169.254/latest/meta-data/', 'fin1te\SafeCurl\Exception\InvalidURLException\InvalidIPException', 'Provided host "169.254.169.254" resolves to "169.254.169.254", which matches a blacklisted value: 169.254.0.0/16'),
            array('ftp://myhost.com', 'fin1te\SafeCurl\Exception\InvalidURLException\InvalidSchemeException', 'Provided scheme "ftp" doesn\'t match whitelisted values: http, https'),
            array('http://user:pass@safecurl.fin1te.net?@google.com/', 'fin1te\SafeCurl\Exception\InvalidURLException', 'Credentials passed in but "sendCredentials" is set to false'),
        );
    }

    /**
     * @dataProvider dataForBlockedUrl
     */
    public function testBlockedUrl($url, $exception, $message)
    {
        $this->setExpectedException($exception, $message);

        $safeCurl = new SafeCurl(curl_init());
        $safeCurl->execute($url);
    }

    public function dataForBlockedUrlByOptions()
    {
        return array(
            array('http://login:password@google.fr', 'fin1te\SafeCurl\Exception\InvalidURLException', 'Credentials passed in but "sendCredentials" is set to false'),
            array('http://safecurl.fin1te.net', 'fin1te\SafeCurl\Exception\InvalidURLException', 'Provided host "safecurl.fin1te.net" matches a blacklisted value'),
        );
    }

    /**
     * @dataProvider dataForBlockedUrlByOptions
     */
    public function testBlockedUrlByOptions($url, $exception, $message)
    {
        $this->setExpectedException($exception, $message);

        $options = new Options();
        $options->addToList('blacklist', 'domain', '(.*)\.fin1te\.net');
        $options->addToList('whitelist', 'scheme', 'ftp');
        $options->disableSendCredentials();

        $safeCurl = new SafeCurl(curl_init(), $options);
        $safeCurl->execute($url);
    }

    public function testWithPinDnsEnabled()
    {
        $options = new Options();
        $options->enablePinDns();

        $safeCurl = new SafeCurl(curl_init(), $options);
        $response = $safeCurl->execute('http://google.com');

        $this->assertNotEmpty($response);
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception
     * @expectedExceptionMessage Redirect limit "1" hit
     */
    public function testWithFollowLocationLimit()
    {
        $options = new Options();
        $options->enableFollowLocation();
        $options->setFollowLocationLimit(1);

        $safeCurl = new SafeCurl(curl_init(), $options);
        $safeCurl->execute('http://t.co/5AMOLpSq3v');
    }

    public function testWithFollowLocation()
    {
        $options = new Options();
        $options->enableFollowLocation();

        $safeCurl = new SafeCurl(curl_init(), $options);
        $response = $safeCurl->execute('http://t.co/5AMOLpSq3v');

        $this->assertNotEmpty($response);
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception\InvalidURLException\InvalidPortException
     * @expectedExceptionMessage Provided port "123" doesn't match whitelisted values: 80, 443, 8080
     */
    public function testWithFollowLocationLeadingToABlockedUrl()
    {
        $options = new Options();
        $options->enableFollowLocation();

        $safeCurl = new SafeCurl(curl_init(), $options);
        // this bit.ly redirect to `http://0.0.0.0:123`
        $safeCurl->execute('http://bit.ly/1L9Ttv0');
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception
     * @expectedExceptionMessage cURL Error: Operation timed out after
     */
    public function testWithCurlTimeout()
    {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_TIMEOUT, 1);

        $safeCurl = new SafeCurl($handle);
        $safeCurl->execute('http://hostname.fr');
    }
}

<?php

use fin1te\SafeCurl\Url;
use fin1te\SafeCurl\Options;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    public function dataForValidate()
    {
        return array(
            array(null, 'fin1te\SafeCurl\Exception\InvalidURLException', 'Provided URL "" cannot be empty'),
            array('http://user@:80', 'fin1te\SafeCurl\Exception\InvalidURLException', 'Error parsing URL "http://user@:80"'),
            array('http:///example.com/', 'fin1te\SafeCurl\Exception\InvalidURLException', 'Error parsing URL "http:///example.com/"'),
            array('http://:80', 'fin1te\SafeCurl\Exception\InvalidURLException', 'Error parsing URL "http://:80"'),
            array('/nohost', 'fin1te\SafeCurl\Exception\InvalidURLException', 'Provided URL "/nohost" doesn\'t contain a hostname'),
            array('ftp://domain.io', 'fin1te\SafeCurl\Exception\InvalidURLException\InvalidSchemeException', 'Provided scheme "ftp" doesn\'t match whitelisted values: http, https'),
            array('http://domain.io:22', 'fin1te\SafeCurl\Exception\InvalidURLException\InvalidPortException', 'Provided port "22" doesn\'t match whitelisted values: 80, 443, 8080'),
            array('http://login:password@google.fr:80', 'fin1te\SafeCurl\Exception\InvalidURLException', 'Credentials passed in but "sendCredentials" is set to false'),
        );
    }

    /**
     * @dataProvider dataForValidate
     */
    public function testValidateUrl($url, $exception, $message)
    {
        $this->setExpectedException($exception, $message);

        Url::validateUrl($url, new Options());
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception\InvalidURLException\InvalidSchemeException
     * @expectedExceptionMessage Provided scheme "http" matches a blacklisted value
     */
    public function testValidateScheme()
    {
        $options = new Options();
        $options->addToList('blacklist', 'scheme', 'http');

        Url::validateUrl('http://www.fin1te.net', $options);
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception\InvalidURLException\InvalidPortException
     * @expectedExceptionMessage Provided port "8080" matches a blacklisted value
     */
    public function testValidatePort()
    {
        $options = new Options();
        $options->addToList('blacklist', 'port', '8080');

        Url::validateUrl('http://www.fin1te.net:8080', $options);
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception\InvalidURLException\InvalidDomainException
     * @expectedExceptionMessage Provided host "www.fin1te.net" matches a blacklisted value
     */
    public function testValidateHostBlacklist()
    {
        $options = new Options();
        $options->addToList('blacklist', 'domain', '(.*)\.fin1te\.net');

        Url::validateUrl('http://www.fin1te.net', $options);
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception\InvalidURLException\InvalidDomainException
     * @expectedExceptionMessage Provided host "www.google.fr" doesn't match whitelisted values: (.*)\.fin1te\.net
     */
    public function testValidateHostWhitelist()
    {
        $options = new Options();
        $options->addToList('whitelist', 'domain', '(.*)\.fin1te\.net');

        Url::validateUrl('http://www.google.fr', $options);
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception\InvalidURLException\InvalidDomainException
     * @expectedExceptionMessage Provided host "www.youpi.boom" doesn't resolve to an IP address
     */
    public function testValidateHostWithnoip()
    {
        $options = new Options();

        Url::validateUrl('http://www.youpi.boom', $options);
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception\InvalidURLException\InvalidIPException
     * @expectedExceptionMessage Provided host "2.2.2.2" resolves to "2.2.2.2", which doesn't match whitelisted values: 1.1.1.1
     */
    public function testValidateHostWithWhitelistIp()
    {
        $options = new Options();
        $options->addToList('whitelist', 'ip', '1.1.1.1');

        Url::validateUrl('http://2.2.2.2', $options);
    }

    public function testValidateHostWithWhitelistIpOk()
    {
        $options = new Options();
        $options->addToList('whitelist', 'ip', '1.1.1.1');

        $res = Url::validateUrl('http://1.1.1.1', $options);

        $this->assertCount(3, $res);
        $this->assertArrayHasKey('url', $res);
        $this->assertArrayHasKey('host', $res);
        $this->assertArrayHasKey('ips', $res);
        $this->assertArrayHasKey(0, $res['ips']);
    }

    /**
     * @expectedException fin1te\SafeCurl\Exception\InvalidURLException\InvalidIPException
     * @expectedExceptionMessage Provided host "1.1.1.1" resolves to "1.1.1.1", which matches a blacklisted value: 1.1.1.1
     */
    public function testValidateHostWithBlacklistIp()
    {
        $options = new Options();
        $options->addToList('blacklist', 'ip', '1.1.1.1');

        Url::validateUrl('http://1.1.1.1', $options);
    }

    public function testValidateUrlOk()
    {
        $options = new Options();
        $options->enablePinDns();

        $res = Url::validateUrl('http://www.fin1te.net:8080', $options);

        $this->assertCount(3, $res);
        $this->assertArrayHasKey('url', $res);
        $this->assertArrayHasKey('host', $res);
        $this->assertArrayHasKey('ips', $res);
        $this->assertArrayHasKey(0, $res['ips']);
        $this->assertEquals('http://37.48.73.92:8080', $res['url']);
        $this->assertEquals('www.fin1te.net', $res['host']);

        $res = Url::validateUrl('http://www.fin1te.net:8080', new Options());

        $this->assertCount(3, $res);
        $this->assertArrayHasKey('url', $res);
        $this->assertArrayHasKey('host', $res);
        $this->assertArrayHasKey('ips', $res);
        $this->assertArrayHasKey(0, $res['ips']);
        $this->assertEquals('http://www.fin1te.net:8080', $res['url']);
        $this->assertEquals('www.fin1te.net', $res['host']);
    }
}

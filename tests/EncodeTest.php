<?php

use Dormilich\WebService\FraudRecord\WebService;
use Test\MockClient;

/*
    @see https://fraudrecord.com/developers/#dv2 for a detailed explanation 
    about the data treatment. 
 */

class EncodeTest extends PHPUnit_Framework_TestCase
{
    public function testSystemParametersAreNotEncoded()
    {
        $client = new MockClient('OK:GO');
        $api = new WebService($client, '12345');

        $api->report(['_type' => 'fraud']);
        parse_str($client->body, $params);

        $this->assertArrayHasKey('_type', $params);
        $this->assertSame('fraud', $params['_type']);
    }

    // values taken straight from the docs
    public function testPasswordsAreHashedOnly()
    {
        $client = new MockClient('OK:GO');
        $api = new WebService($client, '12345');

        $api->report(['password' => 'iLoveLinux!']);
        parse_str($client->body, $params);

        $this->assertArrayHasKey('password', $params);
        $this->assertSame('93491c2dff7b35528c319f304b0222fc55ebcfcb', $params['password']);
    }

    // values taken straight from the docs
    public function testDataAreStrippedAndHashed()
    {
        $client = new MockClient('OK:GO');
        $api = new WebService($client, '12345');

        $api->report([
            'name' => 'John Smith', 
            'phone' => '+1 000 111 22 33 ',
        ]);
        parse_str($client->body, $params);

        $this->assertArrayHasKey('name', $params);
        $this->assertSame('ac2c739924bf5d4d9bf5875dc70274fef0fe54cf', $params['name']);

        $this->assertArrayHasKey('phone', $params);
        $this->assertSame('3f09086d8d4e4019eb534ce28e6b64c8ef563ec9', $params['phone']);
    }

    public function testUrlsAreStrippedToHostName()
    {
        $client = new MockClient('OK:GO');
        $api = new WebService($client, '12345');

        $api->report(['myspace' => 'http://www.example.com/fraudrecord']);
        parse_str($client->body, $params);

        $this->assertArrayHasKey('myspace', $params);
        // hash of "example.com"
        $this->assertSame('ff07748b4d4b8f08f21499e078ef792fded46641', $params['myspace']);
    }

    /**
     * @requires function iconv
     */
    public function testDataValuesAreConvertedToAscii()
    {
        $client = new MockClient('OK:GO');
        $api = new WebService($client, '12345');

        $api->enableIconv(true);
        $api->report([
            'name' => 'François Duprée', 
        ]);
        parse_str($client->body, $params);

        $this->assertArrayHasKey('name', $params);
        // hash of "francoisdupr'ee"
        $this->assertSame('bf2f4c54e91f5b7f83b67bc52ef7c123cc0b5a37', $params['name']);
    }

    /**
     * @requires function iconv
     */
    public function testSkipAsciiConversion()
    {
        $client = new MockClient('OK:GO');
        $api = new WebService($client, '12345');

        $api->report([
            'name' => 'Søren Übermaß', 
        ]);
        parse_str($client->body, $params);

        $this->assertArrayHasKey('name', $params);
        // handling of non-ASCII characters may differ among systems so I 
        // cannot guarantee that the intended "correct" hash is the same for 
        // all systems 
        $this->assertNotEquals('bf2f4c54e91f5b7f83b67bc52ef7c123cc0b5a37', $params['name']);
    }
}

<?php

use Dormilich\WebService\FraudRecord\WebService;
use Test\MockClient;

/*
    The examples are intended to demonstrate the intended use.
    Example values are taken from the documentation, where possible.
 */

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testReportIsPassedNexessaryVariables()
    {
        $client = new MockClient('OK:ea864c03abd2ce90');
        $api = new WebService($client, 'a51ff508c331b7e9');

        // most of these values are taken from the docs
        $api->report([
            '_type'  => 'chargeback',
            '_text'  => 'This client made a chargeback after 3 months of server use.',
            '_value' =>  6,
            'name'   => 'John Smith',
            'email'  => 'john.smith@example.com',
            'ip'     => '192.168.2.1',
        ]);
        parse_str($client->body, $params);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://www.fraudrecord.com/api/', $client->url);

        $this->assertArrayHasKey('_action', $params);
        $this->assertArrayHasKey('_api', $params);
        $this->assertArrayHasKey('_type', $params);
        $this->assertArrayHasKey('_text', $params);
        $this->assertArrayHasKey('_value', $params);
        $this->assertArrayHasKey('name', $params);
        $this->assertArrayHasKey('email', $params);
        $this->assertArrayHasKey('ip', $params);

        $this->assertCount(8, $params);

        $this->assertSame('report', $params['_action']);
        $this->assertSame('a51ff508c331b7e9', $params['_api']);
    }

    public function testQueryIsPassedNexessaryVariables()
    {
        $client = new MockClient('<report>14-3-6.7-abd2ce90ea864c03</report>');
        $api = new WebService($client, 'a51ff508c331b7e9');

        $api->query([
            'name'   => 'John Smith',
            'email'  => 'john.smith@example.com',
            'ip'     => '192.168.2.1',
        ]);
        parse_str($client->body, $params);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://www.fraudrecord.com/api/', $client->url);

        $this->assertArrayHasKey('_action', $params);
        $this->assertArrayHasKey('_api', $params);
        $this->assertArrayHasKey('name', $params);
        $this->assertArrayHasKey('email', $params);
        $this->assertArrayHasKey('ip', $params);

        $this->assertCount(5, $params);

        $this->assertSame('query', $params['_action']);
        $this->assertSame('a51ff508c331b7e9', $params['_api']);
    }

    public function testDeleteIsPassedNexessaryVariables()
    {
        $client = new MockClient('');
        $api = new WebService($client, 'a51ff508c331b7e9');

        $api->delete('ea864c03abd2ce90');
        parse_str($client->body, $params);

        $this->assertSame('POST', $client->method);
        $this->assertSame('https://www.fraudrecord.com/api/', $client->url);

        $this->assertArrayHasKey('_action', $params);
        $this->assertArrayHasKey('_api', $params);
        $this->assertArrayHasKey('_code', $params);

        $this->assertCount(3, $params);

        $this->assertSame('delete', $params['_action']);
        $this->assertSame('a51ff508c331b7e9', $params['_api']);
        $this->assertSame('ea864c03abd2ce90', $params['_code']);
    }
}

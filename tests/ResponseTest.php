<?php

use Dormilich\WebService\FraudRecord\WebService;
use Test\MockClient;

/*
    Since PHPUnit cannot stub an interface, I can as well use a mock 
    implementation that I can use as spy for inspecting the data that are 
    passed to the client implementation.
 */

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testSuccessfulReportReturnsReportCode()
    {
        $client = new MockClient('OK:c41eafdd4e9d6b4e');
        $api = new WebService($client, '12345');

        $code = $api->report(['name' => '55e188e687ba3472']);

        $this->assertSame('c41eafdd4e9d6b4e', $code);
    }

    /**
     * @expectedException Dormilich\WebService\FraudRecord\Exceptions\ResponseException
     * @expectedExceptionMessage NOT-APPROVED
     */
    public function testFailedReportThrowsException()
    {
        $client = new MockClient('ERR:NOT-APPROVED');
        $api = new WebService($client, '12345');

        $api->report(['name' => '55e188e687ba3472']);
    }

    /**
     * @expectedException Dormilich\WebService\FraudRecord\Exceptions\ResponseException
     * @expectedExceptionMessage NODATA
     */
    public function testReportWithoutDataThrowsException()
    {
        // not sure why FraudRecord decided to use an error response without the ERR: prefix
        $client = new MockClient('NODATA');
        $api = new WebService($client, '12345');

        $api->report([]);
    }

    public function testSuccessfulQueryReturnsResultObject()
    {
        $client = new MockClient('<report>14-3-6.7-cf2b27b5556c2ddc</report>');
        $api = new WebService($client, '12345');

        $result = $api->query(['name' => '55e188e687ba3472']);

        $this->assertInstanceOf('Dormilich\\WebService\\FraudRecord\\QueryResult', $result);

        $this->assertSame(14, $result['value']);
        $this->assertSame(3, $result['count']);
        $this->assertSame(6.7, $result['reliability']);
        $this->assertSame('cf2b27b5556c2ddc', $result['code']);
    }

    /**
     * @expectedException Dormilich\WebService\FraudRecord\Exceptions\ResponseException
     * @expectedExceptionMessage API
     */
    public function testFailedQueryThrowsException()
    {
        $client = new MockClient('ERR:API');
        $api = new WebService($client, '12345');

        $api->query(['name' => '55e188e687ba3472']);
    }

    /**
     * @expectedException Dormilich\WebService\FraudRecord\Exceptions\ResponseException
     * @expectedExceptionMessage NODATA
     */
    public function testQueryWithoutDataThrowsException()
    {
        // not sure why FraudRecord decided to use an error response without the ERR: prefix
        $client = new MockClient('NODATA');
        $api = new WebService($client, '12345');

        $api->query([]);
    }

    // there is no documented response for the delete action
}

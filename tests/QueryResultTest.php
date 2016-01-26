<?php

use Dormilich\WebService\FraudRecord\QueryResult;

/*
    @see https://fraudrecord.com/developers/#dv2 for a detailed explanation 
    about the data treatment. 
 */

class QueryResultTest extends PHPUnit_Framework_TestCase
{
    public function testResultObjectHasArrayAccess()
    {
        $result = new QueryResult(10, 2, 3.5, 'c41eafdd4e9d6b4e');

        $this->assertInstanceOf('ArrayAccess', $result);
    }

    public function testResultObjectParameters()
    {
        $result = new QueryResult('10', '2', '3.5', 'c41eafdd4e9d6b4e');

        $this->assertSame(10, $result['value']);
        $this->assertSame(2, $result['count']);
        $this->assertSame(3.5, $result['reliability']);
        $this->assertSame('c41eafdd4e9d6b4e', $result['code']);
    }

    public function testResultObjectHasStringRepresentation()
    {
        $result = new QueryResult(10, 2, 3.5, 'c41eafdd4e9d6b4e');

        $this->assertSame('c41eafdd4e9d6b4e', (string) $result);
    }

    // value

    public function testResultParameterValueMustBeInteger()
    {
        $result = new QueryResult(3.14, 2, 3.5, 'c41eafdd4e9d6b4e');

        $this->assertFalse($result['value']);
    }

    public function testResultParameterValueMustBePositive()
    {
        $result = new QueryResult(-5, 2, 3.5, 'c41eafdd4e9d6b4e');

        $this->assertFalse($result['value']);
    }

    public function testResultParameterValueHasNoUpperLimit()
    {
        $result = new QueryResult(mt_getrandmax(), 2, 3.5, 'c41eafdd4e9d6b4e');

        $this->assertNotFalse($result['value']);
    }

    // count

    public function testResultParameterCountMustBeInteger()
    {
        $result = new QueryResult(10, 3.14, 3.5, 'c41eafdd4e9d6b4e');

        $this->assertFalse($result['count']);
    }

    public function testResultParameterCountMustBePositive()
    {
        $result = new QueryResult(10, -2, 3.5, 'c41eafdd4e9d6b4e');

        $this->assertFalse($result['count']);
    }

    public function testResultParameterCountHasNoUpperLimit()
    {
        $result = new QueryResult(10, mt_getrandmax(), 3.5, 'c41eafdd4e9d6b4e');

        $this->assertNotFalse($result['count']);
    }

    // reliability

    public function testResultParameterReliabilityMustBeFloat()
    {
        $result = new QueryResult(10, 2, 'abc', 'c41eafdd4e9d6b4e');

        $this->assertFalse($result['reliability']);
    }

    // code

    public function testResultParameterCodeMustBeHexadecimal()
    {
        $result = new QueryResult(10, 2, 'abc', 'The quick brown fox ...');

        $this->assertFalse($result['code']);
    }

    // immutability

    public function testResultObjectIsImmutable()
    {
        $result = new QueryResult(10, 2, 3.5, 'c41eafdd4e9d6b4e');

        $result['value'] = 15;
        $result['count'] = null;
        unset($result['code']);

        $this->assertSame(10, $result['value']);
        $this->assertSame(3.5, $result['reliability']);
        $this->assertSame('c41eafdd4e9d6b4e', $result['code']);
    }
}

<?php

namespace Test;

use Dormilich\WebService\Adapter\ClientAdapter;

/**
 * This class is a mock object for the connection client allowing us to inspect 
 * the parameters passed to the object. It is instantiated with the result of 
 * the request() method.
 */
class MockClient implements ClientAdapter
{
    public $method;
    public $url;
    public $body;

    protected $base;
    protected $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function setBaseUri($uri)
    {
        $this->base = $uri;
    }

    public function request($method, $path, array $headers = NULL, $body = NULL)
    {
        $this->url    = $this->createUrl($path);
        $this->method = $method;
        $this->body   = $body;

        if (is_callable($this->response)) {
            return call_user_func($this->response, $method, $this->url, $headers, $body);
        }
        return $this->response;
    }

    protected function createUrl($path)
    {
        $host   = parse_url($this->base, \PHP_URL_HOST);
        $scheme = parse_url($this->base, \PHP_URL_SCHEME);
        $dir    = substr($this->base, 0, strrpos($this->base, '/', strlen($host))+1);

        if (strpos($path, '//') === 0) {
            return $scheme . '://' . $path;
        }
        if (strpos($path, '/') === 0) {
            return $scheme . '://' . $host . $path;
        }
        return $dir . $path;
    }
}

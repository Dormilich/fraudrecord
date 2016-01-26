<?php

namespace Dormilich\WebService\FraudRecord;

use Dormilich\WebService\Adapter\ClientAdapter;
use Dormilich\WebService\FraudRecord\Exceptions\ResponseException;
use Dormilich\WebService\FraudRecord\Exceptions\ValidationException;

class WebService
{
    const HOST = 'https://www.fraudrecord.com/api/';

    protected $iconv = true;

    protected $config = [];

    protected $client;

    /**
     * Set up the FraudRecord web service.
     * 
     * @param ClientAdapter $client A connection adapter.
     * @param string $apiKey The user’s API key.
     * @return self
     */
    public function __construct(ClientAdapter $client, $apiKey)
    {
        $this->client = $client;
        $this->client->setBaseUri(self::HOST);
        $this->config['_api'] = (string) $apiKey;
        $this->iconv = function_exists('iconv');
    }

    /**
     * Enable/disable transliteration of values to ASCII. This has only effect 
     * if the iconv module is installed.
     * 
     * @param bool $bool Set to FALSE to disable ASCII conversion.
     * @return bool Current setting.
     */
    public function enableIconv($bool = true)
    {
        if (function_exists('iconv')) {
            $this->iconv = filter_var($bool, \FILTER_VALIDATE_BOOLEAN);
        }
        return $this->iconv;
    }

    /**
     * Pass data to the client implementation.
     * 
     * @param string $action One of 'query', 'report', or 'delete'.
     * @param array $fields The (data) fields to pass to the API.
     * @return string API response.
     */
    protected function submit($action, array $fields)
    {
        $data = $this->encode($fields) + $this->config + ['_action' => $action];

        $response = $this->client->request('POST', '', [], http_build_query($data));

        sscanf($response, 'ERR:%s', $error);
        if ($error) {
            throw new ResponseException($error);
        }

        return $response;
    }

    /**
     * Request data about a client using client-specific data.
     * 
     * @see https://fraudrecord.com/developers/#dv1
     * 
     * @param array $fields Plain text data array.
     * @return array Client statistics from FraudRecord.
     * @throws 
     */
    public function query(array $fields)
    {
        $response = $this->submit('query', $fields);

        // @see http://php.net/manual/en/function.sscanf.php#56076
        // sscanf() seems to ignore text that is supposed to be after the last identifier
        // in this case using "%s</report>" as format resulted in e.g. "abc</report>" for 
        // that variable, so a stop-character is needed
        sscanf($response, '<report>%d-%d-%f-%[^<]', $value, $count, $reliability, $code);

        if ($value !== NULL) {
            return new QueryResult($value, $count, $reliability, $code);
        }

        throw new ResponseException($response);
    }

    /**
     * Request data about a client using client-specific data.
     * 
     * @see https://fraudrecord.com/developers/#dv1
     * 
     * @param array $fields Plain text data array.
     * @return array Client statistics from FraudRecord.
     * @throws 
     */
    public function report(array $fields)
    {
        $response = $this->submit('report', $fields);

        sscanf($response, 'OK:%s', $code);

        if ($code) {
            return $code;
        }

        throw new ResponseException($response);
    }

    /**
     * Delete a FraudRecord report using the report code from a previous 
     * report action.
     * 
     * @param string $code Report code.
     * @return NULL The delete action does not cause a response.
     * @throws 
     */
    public function delete($code)
    {
        if (!ctype_xdigit($code)) {
            throw new ValidationException('Invalid report code.');
        }

        $this->submit('delete', ['_code' => $code]);

        return NULL;
    }

    /**
     * Encode data variables. System variables are not encoded and fields that 
     * likely are passwords are not transliterated. URLs are stripped down to 
     * the host name and 'www.' is stripped, if present.
     * 
     * @param array $data 
     * @return array
     */
    protected function encode(array $data)
    {
        array_walk($data, function (&$value, $key) {
            if ($key[0] === '_') {
                return;
            }
            if (filter_var($value, \FILTER_VALIDATE_URL)) {
                $value = parse_url($value, \PHP_URL_HOST);
                if (stripos($value, 'www.') === 0) {
                    $value = substr($value, 4);
                }
            }
            elseif (stripos($key, 'pass') === false) {
                $value = $this->toAscii($value);
                $value = $this->trim($value);
            }
            $value = $this->hash($value);
        });
        return $data;
    }

    /**
     * Transliterate the input string into ASCII. The docs do not clearly 
     * mention the treatment of non-ASCII characters but without it, there 
     * would be much less of a chance to match such names.
     * 
     * @param string $string 
     * @return string
     */
    protected function toAscii($string)
    {
        if ($this->iconv) {
            $string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
            $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        }

        return strtolower($string);
    }

    /**
     * Trim a string and remove all spaces.
     * 
     * @param string $value 
     * @return string
     */
    protected function trim($value)
    {
        $value = trim($value);
        $value = str_replace(' ', '', $value);
        return $value;
    }

    /**
     * Hash a string for submission using 32,000 rounds of SHA1 with the 
     * 'fraudrecord-' prefix.
     * 
     * @param string $value 
     * @return string
     */
    protected function hash($value)
    {
        // I preferred array_reduce() were it not 50% slower
        $i = 32000;
        while ($i--) {
            $value = sha1('fraudrecord-' . $value);
        }
        return $value;
    }
}

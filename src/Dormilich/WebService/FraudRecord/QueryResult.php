<?php

namespace Dormilich\WebService\FraudRecord;

class QueryResult implements \ArrayAccess
{
    /**
     * Sum of the reported incidient severities of this client.
     */
    protected $value;
    /**
     * Number of total reports.
     */
    protected $count;
    /**
     * Reliability of the result.
     */
    protected $reliability;
    /**
     * Key to retrieve further report details.
     */
    protected $code = '';

    /**
     * Set the result properties.
     * 
     * @param integer $value Combined severity.
     * @param integer $count Number of reports.
     * @param float $reliability Reliability of the report.
     * @param string $code Report code.
     * @return self
     */
    public function __construct($value, $count, $reliability, $code)
    {
        $this->value = filter_var($value, \FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0]
        ]);
        $this->count = filter_var($count, \FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0]
        ]);
        $this->reliability = filter_var($reliability, \FILTER_VALIDATE_FLOAT);

        // there is no validation filter for hex strings
        if (ctype_xdigit($code)) {
            $this->code = $code;
        }
    }

    /**
     * Check if a property exists.
     * 
     * @see http://php.net/ArrayAccess
     * 
     * @param string $offset Property name.
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * Get the value of a property.
     * 
     * @see http://php.net/ArrayAccess
     * 
     * @param string $offset Property name.
     * @return string
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->$offset;
        }
        return NULL;
    }

    /**
     * Properties are immutable.
     * 
     * @see http://php.net/ArrayAccess
     * 
     * @param string $offset 
     * @param mixed $value 
     * @return void
     */
    public function offsetSet($offset, $value)
    {}

    /**
     * Properties are immutable.
     * 
     * @see http://php.net/ArrayAccess
     * 
     * @param string $offset 
     * @return void
     */
    public function offsetUnset($offset)
    {}

    /**
     * Return the report code.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->code;
    }
}

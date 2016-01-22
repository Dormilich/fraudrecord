<?php

namespace Dormilich\WebService\FraudRecord\Exceptions;

/**
 * Exceptions on validation problems
 */
class ValidationException 
    extends \UnexpectedValueException
    implements FraudRecordException 
{}

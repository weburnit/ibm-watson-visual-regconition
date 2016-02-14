<?php
namespace IBMWatson\Connection\Exceptions;

use IBMWatson\Constants\ExceptionMessages;

class MissingRequiredParameters extends VisualRecognitionException
{
    public function getDefaultMessage()
    {
        return ExceptionMessages::EXCEPTION_INVALID_PARAMETERS;
    }
}

<?php
namespace IBMWatson\Connection\Exceptions;

use IBMWatson\Constants\ExceptionMessages;

class MissingRequiredParameters extends VisualInsightException
{
    public function getDefaultMessage()
    {
        return ExceptionMessages::EXCEPTION_INVALID_PARAMETERS;
    }
}

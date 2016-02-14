<?php
namespace IBMWatson\Connection\Exceptions;

use IBMWatson\Constants\ExceptionMessages;

class GenericHTTPError extends VisualRecognitionException
{

    public function getDefaultMessage()
    {
        return ExceptionMessages::EXCEPTION_GENERIC_HTTP_ERROR;
    }
}

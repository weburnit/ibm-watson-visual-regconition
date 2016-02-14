<?php
namespace IBMWatson\Connection\Exceptions;

use IBMWatson\Constants\ExceptionMessages;

class GenericHTTPError extends VisualInsightException
{

    public function getMessage()
    {
        return ExceptionMessages::EXCEPTION_GENERIC_HTTP_ERROR;
    }
}

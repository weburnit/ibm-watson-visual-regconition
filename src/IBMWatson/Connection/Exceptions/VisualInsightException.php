<?php

namespace IBMWatson\Connection\Exceptions;

use GuzzleHttp\Message\ResponseInterface;

abstract class VisualInsightException extends \Exception
{
    public function __construct(
        $reponse_code = 400,
        ResponseInterface $response = null
    ) {
        $message = '';
        if ($response) {

            $data             = (string)$response->getBody();
            $jsonResponseData = json_decode($data, true);

            $message = isset($jsonResponseData['message']) ? $jsonResponseData['message'] : $this->getDefaultMessage();
        }
        parent::__construct($message, $reponse_code);
    }

    /**
     * @return string return default exception message
     */
    public abstract function getDefaultMessage();
}

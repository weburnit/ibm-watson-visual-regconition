<?PHP

namespace IBMWatson\Connection;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Post\PostBodyInterface;
use GuzzleHttp\Post\PostFile;
use GuzzleHttp\Query;
use IBMWatson\Connection\Exceptions\GenericHTTPError;
use IBMWatson\Connection\Exceptions\InvalidCredentials;
use IBMWatson\Connection\Exceptions\MissingEndpoint;
use IBMWatson\Connection\Exceptions\MissingRequiredParameters;
use IBMWatson\Constants\Api;
use IBMWatson\Constants\ExceptionMessages;

/**
 * This class is a wrapper for the Guzzle (HTTP Client Library).
 */
class RestClient
{

    /**
     * @var Guzzle
     */
    protected $mgClient;

    /**
     * RestClient constructor.
     *
     * @param $username
     * @param $password
     * @param $apiVersion
     */
    public function __construct($username, $password)
    {
        $this->mgClient = new Guzzle(
            [
                'base_url' => Api::IBM_WATSON_VISUAL_INSIGHT_API,
                'defaults' => [
                    'auth'       => [$username, $password],
                    'exceptions' => false,
                    'config'     => ['curl' => [CURLOPT_FORBID_REUSE => true]],
                    'headers'    => [
                        'User-Agent' => Api::SDK_USER_AGENT . '/' . Api::SDK_VERSION,
                    ],
                ],
            ]
        );
    }

    /**
     * @return Guzzle
     */
    public function getClient()
    {
        return $this->mgClient;
    }

    /**
     * @param string $endpointUrl
     * @param array  $postData
     * @param array  $files
     *
     * @return mixed
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    public function post($endpointUrl, $postData = [], $files = [])
    {
        $request = $this->mgClient->createRequest('POST', $endpointUrl, ['body' => $postData]);
        /** @var \GuzzleHttp\Post\PostBodyInterface $postBody */
        $postBody = $request->getBody();
        $postBody->setAggregator(Query::duplicateAggregator());

        $fields = ['images_file', 'positive_examples', 'negative_examples'];
        foreach ($fields as $fieldName) {
            if (isset($files[$fieldName])) {
                $this->addFile($postBody, $fieldName, $files[$fieldName]);
            }
        }

        $response = $this->mgClient->send($request);

        return $this->responseHandler($response);
    }

    /**
     * @param string $endpointUrl
     * @param array  $queryString
     *
     * @return mixed
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    public function get($endpointUrl, $queryString = [])
    {
        $response = $this->mgClient->get($endpointUrl, ['query' => $queryString]);

        return $this->responseHandler($response);
    }

    /**
     * @param string $endpointUrl
     *
     * @return mixed
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    public function delete($endpointUrl)
    {
        $response = $this->mgClient->delete($endpointUrl);

        return $this->responseHandler($response);
    }

    /**
     * @param ResponseInterface $responseObj
     *
     * @return mixed
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    public function responseHandler($responseObj)
    {
        $httpResponseCode = $responseObj->getStatusCode();
        if ($httpResponseCode === 200) {
            $data             = (string)$responseObj->getBody();
            $jsonResponseData = json_decode($data, true);

            return $jsonResponseData;
        } elseif ($httpResponseCode == 400) {
            throw new MissingRequiredParameters($httpResponseCode, $responseObj);
        } elseif ($httpResponseCode == 401) {
            throw new InvalidCredentials(ExceptionMessages::EXCEPTION_INVALID_CREDENTIALS);
        } else {
            throw new GenericHTTPError(
                $httpResponseCode,
                $responseObj
            );
        }
    }

    /**
     * Add a file to the postBody.
     *
     * @param PostBodyInterface $postBody
     * @param string            $fieldName
     * @param string|array      $filePath
     */
    private function addFile(PostBodyInterface $postBody, $fieldName, $filePath)
    {
        $postBody->addFile(new PostFile($fieldName, fopen($filePath, 'r')));
    }
}

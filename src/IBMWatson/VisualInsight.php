<?PHP

namespace IBMWatson;

use IBMWatson\Connection\RestClient;
use IBMWatson\Constants\Api;
use IBMWatson\Messages\ClassifierBuilder;
use IBMWatson\Messages\Exceptions;
use IBMWatson\Messages\Exceptions\NotEnoughImages;
use IBMWatson\Response\Classification;
use IBMWatson\Response\Classifier;

/**
 * This class is the base class for the IBMWatson SDK.
 * Follow official documentation (link below) for usage instructions.
 *
 * @link https://github.com/weburnit/iw-visual-insight/README.md
 */
class VisualInsight
{

    /**
     * @var RestClient
     */
    protected $restClient;

    /**
     * @var string
     */
    private $version;

    /**
     * VisualInsight constructor.
     *
     * @param $username
     * @param $password
     * @param $version
     */
    public function __construct($username, $password, $version)
    {
        $this->restClient = new RestClient($username, $password);
        $this->setVersion($version);
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->restClient->getClient();
    }

    /**
     * @param              $file
     * @param array        $classifiers
     *
     * @return Classification
     */
    public function classifyImage($file, array $classifiers)
    {
        $ids                   = [];
        $ids['classifier_ids'] = $classifiers;

        $response = $this->restClient->post(
            $this->versioningURI(Api::API_URI_CLASSIFY),
            ['classifier_ids' => json_encode($ids)],
            ['images_file' => $file]
        );

        return new Classification($response);
    }

    /**
     * @param array $files
     * @param array $classifiers
     *
     * @return Classification
     */
    public function classifyImages(array $files, array $classifiers)
    {
        $builder               = new ClassifierBuilder();
        $ids                   = [];
        $ids['classifier_ids'] = $classifiers;

        $zipFile = $builder->compressFiles($files, getcwd() . DIRECTORY_SEPARATOR . time() . 'images.zip');

        $response = $this->restClient->post(
            $this->versioningURI(Api::API_URI_CLASSIFIERS),
            ['classifier_ids' => json_encode($ids)],
            ['images_file' => $zipFile]
        );

        @unlink($zipFile);

        return new Classification($response);
    }

    /**
     * @return mixed
     */
    public function getClassifiers()
    {
        $uri      = $this->versioningURI(Api::API_URI_CLASSIFIERS);
        $response = $this->restClient->get($uri);

        return $response;
    }

    /**
     * @param $id
     *
     * @return \stdClass
     */
    public function deleteClassifiers($id)
    {
        $response = $this->restClient->delete($this->versioningURI(sprintf('%s/%s', Api::API_URI_CLASSIFIERS, $id)));

        return $response;
    }

    /**
     * @param $name
     * @param $positiveFiles
     * @param $negativeFiles
     *
     * @return Classifier
     * @throws NotEnoughImages
     */
    public function createClassifier($name, array $positiveFiles, array $negativeFiles)
    {
        $builder = new ClassifierBuilder();

        if (count($positiveFiles) < 50 || count($negativeFiles) < 50) {
            throw new NotEnoughImages(
                sprintf(
                    'Training your classifier requires more than 50 images(negative: %d, positive: %d)',
                    count($positiveFiles),
                    count($negativeFiles)
                )
            );
        }

        $positiveZipFiles = $builder->compressFiles(
            $positiveFiles,
            getcwd() . DIRECTORY_SEPARATOR . time() . 'positive_images.zip'
        );
        $negativeZipFiles = $builder->compressFiles(
            $negativeFiles,
            getcwd() . DIRECTORY_SEPARATOR . time() . 'negative_images.zip'
        );

        $response = $this->restClient->post(
            $this->versioningURI(Api::API_URI_CLASSIFIERS),
            ['name' => $name],
            [
                'positive_examples' => $positiveZipFiles,
                'negative_examples' => $negativeZipFiles
            ]
        );

        @unlink($positiveZipFiles);
        @unlink($negativeZipFiles);

        return new Classifier($response);
    }

    private function versioningURI($uri)
    {
        return sprintf('%s/%s%s?version=%s', Api::IBM_WATSON_BASE_URI, Api::API_VERSION, $uri, $this->version);
    }

    /**
     * @param string $version
     *
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

}

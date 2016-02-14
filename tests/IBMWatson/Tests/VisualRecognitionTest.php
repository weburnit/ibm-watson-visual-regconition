<?php

namespace IBMWatson\Tests;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use IBMWatson\Response\Classification;
use IBMWatson\Response\Classifier;
use IBMWatson\VisualRecognition;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;

class VisualRecognitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileSystem
     */
    private $fileSystem;

    /**
     * @var VisualRecognition
     */
    private $service;

    public function setUp()
    {
        // Create and mount the file system
        $fs = FileSystem::factory('weburnit://');
        $fs->mount();
        $dir = new Directory(['sample.png' => new File('xxxxx')]);
        $fs->get('/')->add('folder', $dir);
        $this->fileSystem = $fs;

        $this->service = new VisualRecognition('78ba7889-e331-4f58-958b-254f9231b1d2', 'MHsln6fPvCEp', '2015-12-02');
    }

    protected function tearDown()
    {
        $this->fileSystem->unmount();
    }

    /**
     * @expectedException \IBMWatson\Connection\Exceptions\InvalidCredentials
     */
    public function testInvalidCredentials()
    {
        $mock = new Mock(
            [
                new Response(401, [])
            ]
        );

        $service = $this->service;
        $service->getClient()->getEmitter()->attach($mock);

        $service->getClassifiers();
    }

    /**
     * @expectedException \IBMWatson\Connection\Exceptions\MissingRequiredParameters
     * @expectedExceptionMessageRegExp /(Parameter is not valid|Your parameters does not meet API requirement)/
     * @dataProvider                   dataMissingException
     */
    public function testMissingRequiredParameters($mock)
    {
        $service = $this->service;
        $service->getClient()->getEmitter()->attach($mock);

        $service->getClassifiers();
    }

    public function dataMissingException()
    {
        return [
            [
                new Mock(
                    [
                        new Response(400, [], Stream::factory('{"error": "400", "message": "Parameter is not valid"}'))
                    ]
                )
            ],
            [
                new Mock(
                    [
                        new Response(400, [])
                    ]
                )
            ]
        ];
    }

    /**
     * @expectedException \IBMWatson\Connection\Exceptions\GenericHTTPError
     * @expectedExceptionMessageRegExp /(Cannot get classifiers|An HTTP Error has occurred! Check your network connection and try again)/
     * @dataProvider                   dataUnknownException
     */
    public function testUnknownError($mock)
    {
        $service = $this->service;
        $service->getClient()->getEmitter()->attach($mock);

        $service->getClassifiers();
    }

    public function dataUnknownException()
    {
        return [
            [
                new Mock(
                    [
                        new Response(403, [], Stream::factory('{"error": "400", "message": "Cannot get classifiers"}'))
                    ]
                )
            ],
            [
                new Mock(
                    [
                        new Response(403, [])
                    ]
                )
            ]
        ];
    }

    /**
     * Test classify single image
     */
    public function testClassifyImage()
    {
        $classifierJson = '{
                              "images": [
                                {
                                  "image": "test.jpg",
                                  "scores": [
                                    {
                                      "classifier_id": "sports",
                                      "name": "Sports",
                                      "score": 0.700104
                                    },
                                    {
                                      "classifier_id": "cricket_1234",
                                      "name": "Cricket",
                                      "score": 0.689532
                                    }
                                  ]
                                }
                              ]
                            }';

        $mock = new Mock(
            [
                new Response(200, [], Stream::factory($classifierJson))
            ]
        );

        $service = $this->service;

        $service->getClient()->getEmitter()->attach($mock);

        /**
         * @var $result Classification
         */
        $result = $service->classifyImage('weburnit://folder/sample.png', ['acne_1234151234']);

        $this->assertInstanceOf(Classification::class, $result);

        $this->assertEquals(2, count($result->getScores()));
        $this->assertEquals('test.jpg', $result->getImage());

        $score = $result->getScores()[0];
        $this->assertEquals('sports', $score->getClassifierId());
        $this->assertEquals('Sports', $score->getName());
        $this->assertGreaterThan(70, $score->getMatch());
    }

    /**
     * Test classify multiple images
     */
    public function testClassifyImages()
    {
        $classifierJson = '{
                              "images": [
                                {
                                  "image": "test.jpg",
                                  "scores": [
                                    {
                                      "classifier_id": "sports",
                                      "name": "Sports",
                                      "score": 0.700104
                                    },
                                    {
                                      "classifier_id": "cricket_1234",
                                      "name": "Cricket",
                                      "score": 0.689532
                                    },
                                    {
                                      "classifier_id": "sports",
                                      "name": "Sports",
                                      "score": 0.700104
                                    },
                                    {
                                      "classifier_id": "cricket_1234",
                                      "name": "Cricket",
                                      "score": 0.689532
                                    }
                                  ]
                                }
                              ]
                            }';

        $mock = new Mock(
            [
                new Response(200, [], Stream::factory($classifierJson))
            ]
        );

        $service = $this->service;

        $service->getClient()->getEmitter()->attach($mock);

        /**
         * @var $result Classification
         */
        $result = $service->classifyImages($this->getMockFiles(4), ['acne_1234151234']);

        $this->assertInstanceOf(Classification::class, $result);
        $this->assertEquals(4, count($result->getScores()));
    }

    public function testGetClassifiers()
    {

        $classifiersJson = '{ "classifiers":[
        {"classifier_id":"Car_393026091","name":"Car"},
        {"classifier_id":"Black","name":"Black"},
        {"classifier_id":"Blue","name":"Blue"},
        {"classifier_id":"Brown","name":"Brown"},
        {"classifier_id":"Cyan","name":"Cyan"},
        {"classifier_id":"Green","name":"Green"},
        {"classifier_id":"Magenta","name":"Magenta"},
        {"classifier_id":"Mixed_Color","name":"Mixed_Color"},
        {"classifier_id":"Orange","name":"Orange"},
        {"classifier_id":"Red","name":"Red"},
        {"classifier_id":"Violet","name":"Violet"}]}';

        $mock = new Mock(
            [
                new Response(200, [], Stream::factory($classifiersJson))
            ]
        );

        $service = $this->service;

        $service->getClient()->getEmitter()->attach($mock);

        $results = $service->getClassifiers();

        $this->assertArrayHasKey('classifiers', $results);
        $this->assertEquals(11, count($results['classifiers']));
    }

    public function testDeleteClassifier()
    {
        $mock = new Mock(
            [
                new Response(200, [])
            ]
        );

        $service = $this->service;

        $service->getClient()->getEmitter()->attach($mock);

        $response = $service->deleteClassifiers('Black');

        $this->assertNull($response);
    }

    public function testCreateClassifier()
    {
        $mock = new Mock(
            [
                new Response(
                    200, [], Stream::factory(
                    '{
                      "name": "tiger",
                      "classifier_id": "tiger_1234",
                      "created": "2015-11-23 17:43:11+00:00",
                      "owner": "dkfj32-al543-324js-382js"
                    }'
                )
                )
            ]
        );

        $service = $this->service;

        $service->getClient()->getEmitter()->attach($mock);

        $result = $service->createClassifier('tiger', $this->getMockFiles(50), $this->getMockFiles(50));

        $this->assertInstanceOf(Classifier::class, $result);
        $this->assertEquals('tiger', $result->getName());
        $this->assertEquals('tiger_1234', $result->getClassifierId());
        $this->assertEquals('2015-11-23 17:43:11+00:00', $result->getCreated());
    }

    /**
     * Unable to Create classifier due to lack of images file
     *
     * @expectedException \IBMWatson\Messages\Exceptions\NotEnoughImages
     * @expectedExceptionMessageRegExp /Training your classifier requires more than 50 images\(negative: \d+, positive: \d+\)/
     */
    public function testCreateClassifierNotEnoughImage()
    {
        $service = $this->service;
        $service->createClassifier('tiger', $this->getMockFiles(20), $this->getMockFiles(20));
    }

    private function getMockFiles($total)
    {
        $folder = sprintf('folder%d', rand(100, 9999));
        $files  = [];
        for ($i = 0; $i < $total; $i++) {
            $folderName = sprintf('%s%d', $folder, $i);
            $dir        = new Directory(['sample.png' => new File('xxxxx')]);
            $this->fileSystem->get('/')->add($folderName, $dir);
            $files[] = sprintf('weburnit://%s/%s', $folderName, 'sample.png');
        }

        return $files;
    }
}

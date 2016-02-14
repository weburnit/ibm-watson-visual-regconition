<?php

namespace IBMWatson\Response;

class Classification
{
    /**
     * @var string
     */
    private $image;

    /**
     * @var Score[]
     */
    private $scores;

    /**
     * Classifier constructor.
     *
     * @param array $responseData
     */
    public function __construct(array $responseData)
    {
        if (isset($responseData['images'])) {
            foreach ($responseData['images'] as $item) {
                $this->image = isset($item['image']) ? $item['image'] : '';
                if (isset($item['scores'])) {
                    foreach ($item['scores'] as $score) {
                        $this->scores[] = new Score($score['classifier_id'], $score['name'], $score['score']);
                    }
                }
            }
        }
    }

    /**
     * @return Score[]
     */
    public function getScores()
    {
        return $this->scores;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }
}

<?php

namespace whm\Smoke\Rules\Json;

use Doctrine\Tests\Common\Annotations\False;
use Peekmo\JsonPath\JsonStore;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if xpath is found in a html document.
 */
class JsonPathExistsRule extends StandardRule
{
    protected $contentTypes = ['json'];

    private $jsonPaths;

    public function init(array $jsonPaths)
    {
        $this->jsonPaths = $jsonPaths;
    }

    /**
     * @param $relation string
     * @param $value int
     * @param $count int
     *
     * @return bool
     */
    private function checkRelation($relation, $value, $count)
    {
        switch ($relation) {
            case 'equals':
                if ($value !== $count) {
                    return false;
                }
                break;
            case 'less than':
                if ($value >= $count) {
                    return false;
                }
                break;
            case 'greater than':
                if ($value <= $count) {
                    return false;
                }
                break;
        }

        return true;
    }

    public function doValidation(Response $response)
    {
        $json = json_decode($response->getBody());
        $store = new JsonStore($json);

        $error = false;
        $noCorrectJsonPaths = array();

        foreach ($this->jsonPaths as $path) {
            $jsonValue = $store->get($path['pattern']);
            $count = count($jsonValue);

            if ($jsonValue === false || (is_array($jsonValue) && empty($jsonValue))) {
                $error = true;
                $noCorrectJsonPaths[] = $path['pattern'] . ' (JSON Path not found)';
            }
            if ($this->checkRelation($path['relation'], $path['value'], $count) === false) {
                $error = true;
                $noCorrectJsonPaths[] = $path['pattern'] . ' (number of JSONPaths is not correct corresponding to the given relation/value)';
            }
        }

        if ($error === true) {
            $allNoCorrectJsonPaths = implode('", "', $noCorrectJsonPaths);
            throw new ValidationFailedException('Disonances with JSON Paths "' . $allNoCorrectJsonPaths . '!');
        }
    }
}

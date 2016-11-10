<?php

namespace whm\Smoke\Rules\Json\JsonSchema;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;
use JsonSchema\Validator;
use JsonSchema\SchemaStorage;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;

/**
 * This rule checks if a JSON file is valid for a JSON schema file
 */
class JsonSchemaRule extends StandardRule
{
    private $jsonSchemaFiles;

    protected $contentTypes = array('json');

    private $json_errors = array(
        JSON_ERROR_NONE => 'No Error',
        JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
    );

    public function init($jsonSchemaFiles)

    {
        $this->jsonSchemaFiles = $jsonSchemaFiles;
    }

    protected function doValidation(Response $response)
    {
        $data = json_decode($response->getBody());
        if ($data === null) {
            throw new ValidationFailedException("The given JSON data can not be validated (last error: '" . $this->json_errors[json_last_error()] . "').");
        }
        else {
            $error = false;
            $messageParts = array();

            foreach ($this->jsonSchemaFiles AS $jsonSchemaFile) {
                $factory = new Factory( null, null, Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_COERCE );
                $validator = new Validator($factory);

                $jsonSchemaObject = (object)json_decode(file_get_contents($jsonSchemaFile['jsonschemafileurl']));
                
                $validator->check($data, $jsonSchemaObject);

                if (!$validator->isValid()) {
                    $error = true;
                    $errorMessage = '';
                    foreach ($validator->getErrors() as $error) {
                        $errorMessage = $errorMessage .  sprintf("[%s] %s\n", $error['property'], $error['message']);
                    }
                    $messageParts[] = $jsonSchemaFile['jsonschemafilename'] . ' - ' . $jsonSchemaFile['jsonschemafileurl'] . '(last error: ' . $errorMessage . ').';
                }
            }

            if ($error == true) {
                $message = 'JSON file (' . (string)$response->getUri() . ')  does not validate against the following JSON Schema files: ' . implode(", ", $messageParts);
                throw new ValidationFailedException($message);
            }
        }
    }
}

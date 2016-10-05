<?php

namespace whm\Smoke\Rules\Xml\Sitemap;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a sitemap.xml file is valid.
 */
class ValidRule extends StandardRule
{
    const SCHEMA = 'schema.xsd';
    const INDEX = 'siteindex.xsd';

    protected $contentTypes = array('text/xml', 'application/xml');

    private function getSchema($isIndex)
    {
        return ($isIndex) ?  __DIR__ . '/' . self::INDEX : __DIR__ . '/' . self::SCHEMA;
    }

    private function validateBody($body, $filename, $isIndex = TRUE)
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($body);

        $valid = @$dom->schemaValidate($this->getSchema($isIndex));
        if (!$valid) {
            $lastError = libxml_get_last_error();
            throw new ValidationFailedException(
                'The given sitemap file (' . $filename . ') did not validate against the sitemap schema (last error: ' . str_replace("\n", '', $lastError->message) . ').');
        }
    }

    /**
     * @param string
     * @return array
     */
    private function getLocations($body)
    {
        $locations = array();
        $xml = simplexml_load_string($body);
        $json = json_encode($xml);
        $xmlValues = json_decode($json, true);

        if (isset($xmlValues['sitemap']['loc'])) {
            $locations[] = $xmlValues['sitemap']['loc'];
        } else {
            foreach ($xmlValues['sitemap'] as $sitemap) {
                $locations[] = $sitemap['loc'];
            }
        }

        return $locations;
    }

    protected function doValidation(Response $response)
    {
        $body = $response->getBody();

        // sitemapindex or urlset
        if (preg_match('/<sitemapindex/', $body)) {

            $this->validateBody($body, (string) $response->getUri());
            
        } elseif (preg_match('/<urlset/', $body)) {
            $this->validateBody($body, (string) $response->getUri(), FALSE);
        }
    }
}

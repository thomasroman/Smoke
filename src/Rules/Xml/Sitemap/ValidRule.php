<?php

namespace whm\Smoke\Rules\Xml\Sitemap;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a sitemap.xml file is valid.
 */
class ValidRule implements Rule
{
    const SCHEMA = 'sitemap0_9.xsd';

    private function getSchema()
    {
        return __DIR__ . '/' . self::SCHEMA;
    }

    private function validateBody ($body) {
        libxml_clear_errors();
        $dom = new \DOMDocument();
        @$dom->loadXML($body);
        $lastError = libxml_get_last_error();
        if ($lastError) {
            throw new ValidationFailedException(
                'The given sitemap file is not well formed (last error: ' . str_replace("\n", '', $lastError->message) . ').');
        }
        $valid = @$dom->schemaValidate($this->getSchema());
        if (!$valid) {
            $lastError = libxml_get_last_error();
            throw new ValidationFailedException(
                'The given sitemap file did not validate vs. sitemap.xsd (last error: ' . str_replace("\n", '', $lastError->message) . ').');
        }
    }

    /**
     * @param string
     * @return array
     */
    private function getLocations($body) {
        $locations = array();
        $xml = simplexml_load_string($body);
        $json = json_encode($xml);
        $xmlValues = json_decode($json, TRUE);

        if (isset($xmlValues['sitemap']['loc'])) {
            $locations[] = $xmlValues['sitemap']['loc'];
        }
        else {
            foreach ($xmlValues['sitemap'] AS $sitemap) {
                $locations[] = $sitemap['loc'];
            }
        }
        return $locations;
    }

    public function validate(Response $response)
    {
        if (strtolower($response->getContentType()) !== 'text/xml') {
            return;
        }
        $body = $response->getBody();

        // sitemapindex or urlset
        if (preg_match('/<sitemapindex/', $body)) {
            $allSingleSitemapsUrls = $this->getLocations($body);
            foreach ($allSingleSitemapsUrls AS $sitemapUrl) {
                $singleSitemapXml = file_get_contents($sitemapUrl);
                $this->validateBody($singleSitemapXml);
            }
        }
        elseif (preg_match('/<urlset/', $body)) {
            $this->validateBody($body);
        }
    }
}

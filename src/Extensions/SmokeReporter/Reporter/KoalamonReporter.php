<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use whm\Smoke\Config\Configuration;
use whm\Smoke\Scanner\Result;

/**
 * Class XUnitReporter.
 */
class KoalamonReporter implements Reporter
{
    /**
     * @var Result[]
     */
    private $results;

    private $koaloMon = "http://www.koalamon.com/app_dev.php/webhook/";
    private $apiKey;
    private $config;

    const STATUS_SUCCESS = "success";
    const STATUS_FAILURE = "failure";

    public function init($apiKey, Configuration $_configuration)
    {
        $this->config = $_configuration;
        $this->apiKey = $apiKey;
    }

    /**
     * @param Rule [];
     */
    private function getRuleKeys()
    {
        $keys = array();
        foreach ($this->config->getRules() as $key => $rule) {
            $keys[] = $key;
        }
        return $keys;
    }

    public function processResult(Result $result)
    {
        $this->results[] = $result;
    }

    public function finish()
    {
        $rules = $this->getRuleKeys();

        foreach ($this->results as $result) {
            $failedTests = array();
            if ($result->isFailure()) {
                foreach ($result->getMessages() as $ruleLKey => $message) {
                    $identifier = "smoke_" . $ruleLKey . "_" . $result->getUrl();
                    $system = str_replace("http://", "", $result->getUrl());
                    $this->send($identifier, $system, 'smoke_' . $ruleLKey, $message, self::STATUS_FAILURE, (string)$result->getUrl());
                    $failedTests[] = $ruleLKey;
                }
            }

            foreach ($rules as $rule) {
                if (!in_array($rule, $failedTests)) {
                    $identifier = "smoke_" . $rule . "_" . $result->getUrl();
                    $system = str_replace("http://", "", $result->getUrl());
                    $this->send($identifier, $system, 'smoke_' . $rule, "", self::STATUS_SUCCESS, (string)$result->getUrl());
                }
            }
        }
    }

    public function send($identifier, $system, $tool, $message, $status, $url = "")
    {
        $curl = curl_init();
        $responseBody = array(
            'system' => $system,
            'status' => $status,
            'message' => $message,
            'identifier' => $identifier,
            'type' => $tool,
            'url' => $url
        );

        $koalamonUrl = $this->koaloMon . "?api_key=" . $this->apiKey;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $koalamonUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($responseBody),
        ));
        $response = curl_exec($curl);

        $err = curl_error($curl);
        curl_close($curl);
        return $err;
    }
}
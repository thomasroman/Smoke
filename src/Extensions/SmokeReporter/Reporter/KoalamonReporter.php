<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
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

    private $koaloMon = 'http://www.koalamon.com/app_dev.php/webhook/';
    private $apiKey;
    private $config;
    private $system;
    private $collect;
    private $identifier;

    private $output;

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    public function init($apiKey, $system, $identifier = "", $collect = true, Configuration $_configuration, OutputInterface $_output)
    {
        $this->config = $_configuration;
        $this->apiKey = $apiKey;
        $this->system = $system;
        $this->collect = $collect;
        $this->identifier = $identifier;

        $this->output = $_output;
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
        $this->output->writeln("Sending results to www.koalamon.com ... \n");

        if ($this->collect) {
            $this->sendCollected();
        } else {
            $this->sendSingle();
        }
    }

    private function sendSingle()
    {
        $rules = $this->getRuleKeys();
        foreach ($this->results as $result) {
            $failedTests = array();
            if ($result->isFailure()) {
                foreach ($result->getMessages() as $ruleLKey => $message) {
                    $identifier = 'smoke_' . $ruleLKey . '_' . $result->getUrl();
                    $this->send($identifier, $this->system, 'smoke', $message, self::STATUS_FAILURE, (string)$result->getUrl());
                    $failedTests[] = $ruleLKey;
                }
            }
            foreach ($rules as $rule) {
                if (!in_array($rule, $failedTests, true)) {
                    $identifier = 'smoke_' . $rule . '_' . $result->getUrl();
                    $this->send($identifier, $this->system, 'smoke_' . $rule, '', self::STATUS_SUCCESS, (string)$result->getUrl());
                }
            }
        }
    }

    private function sendCollected()
    {
        $failureMessages = array();

        foreach ($this->getRuleKeys() as $rule) {
            $failureMessages[$rule] = "";
        }

        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                foreach ($result->getMessages() as $ruleLKey => $message) {
                    if ($failureMessages[$ruleLKey] == "") {
                        $failureMessages[$ruleLKey] = "    The smoke test for " . $this->system . " failed (Rule: " . $ruleLKey . ").<ul>";
                    }
                    $failureMessages[$ruleLKey] .= "<li>" . $message . "(url: " . $result->getUrl() . ")</li>";
                }
            }
        }

        foreach ($failureMessages as $key => $failureMessage) {
            if ($failureMessage != "") {
                $this->send($this->identifier . '_' . $key, $this->system, 'smoke', $failureMessage . '</ul>', self::STATUS_FAILURE, "");
            } else {
                $this->send($this->identifier . '_' . $key, $this->system, 'smoke', "", self::STATUS_SUCCESS, "");
            }
        }
    }

    public function send($identifier, $system, $tool, $message, $status, $url = '')
    {
        $curl = curl_init();
        $responseBody = array(
            'system' => $system,
            'status' => $status,
            'message' => $message,
            'identifier' => $identifier,
            'type' => $tool,
            'url' => $url,
        );

        $koalamonUrl = $this->koaloMon . '?api_key=' . $this->apiKey;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $koalamonUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($responseBody),
        ));
        $response = curl_exec($curl);

        $err = curl_error($curl);
        curl_close($curl);

        return $err;
    }
}

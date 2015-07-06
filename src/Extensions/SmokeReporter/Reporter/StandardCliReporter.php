<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Scanner\Result;

class StandardCliReporter extends CliReporter
{
    /**
     * @var Result[]
     */
    private $results = array();
    private $orderBy;
    private $rules = array();
    private $maxResults;

    public function init(OutputInterface $_output, Configuration $_configuration, $orderBy = 'url', $maxResults = 0)
    {
        $this->setOutputInterface($_output);

        $this->orderBy = $orderBy;
        $this->rules = $_configuration->getRules();

        if ($maxResults === 0) {
            $this->maxResults = 10000000;
        } else {
            $this->maxResults = $maxResults;
        }
    }

    public function processResult(Result $result)
    {
        $this->results[] = $result;
    }

    public function finish()
    {
        if ($this->orderBy === 'url') {
            $this->renderUrlOutput();
        } elseif ($this->orderBy === 'rule') {
            $this->renderRuleOutput();
        }
        $this->output->writeln('');
    }

    private function getFailedUrls($ruleKey)
    {
        $failedUrls = array();

        $count = 0;
        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                if (array_key_exists($ruleKey, $result->getMessages())) {
                    $messages = $result->getMessages();
                    $failedUrls[] = (string) $result->getUrl() . ' - ' . $messages[$ruleKey];
                    ++$count;
                }
                if ($count > $this->maxResults) {
                    $failedUrls[] = '... only the first ' . $this->maxResults . ' elements are shown.';
                    break;
                }
            }
        }

        return $failedUrls;
    }

    private function renderRuleOutput()
    {
        $this->output->writeln("\n\n <comment>Rules and Violations:</comment> \n");

        foreach ($this->rules as $ruleKey => $rule) {
            $info = Init::getInitInformationByClass($rule);
            $failedUrls = $this->getFailedUrls($ruleKey);

            if (count($failedUrls) > 0) {
                $this->output->writeln('  <error> ' . get_class($rule) . ' </error>');
            } else {
                $this->output->writeln('  <info> ' . get_class($rule) . ' </info>');
            }

            $this->output->writeln('   ' . str_replace("\n", "\n   ", $info['documentation']) . "\n");

            foreach ($failedUrls as $failedUrl) {
                $this->output->writeln('   - ' . $failedUrl);
            }

            $this->output->writeln('');
        }
    }

    private function renderUrlOutput()
    {
        $this->output->writeln("\n\n <comment>Passed tests:</comment> \n");

        foreach ($this->results as $result) {
            if ($result->isSuccess()) {
                $this->renderSuccess($result);
            }
        }

        $this->output->writeln("\n <comment>Failed tests:</comment> \n");

        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                $this->renderFailure($result);
            }
        }
    }
}

<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Scanner\Result;

class CliReporter implements Reporter, OutputAwareReporter, ConfigAwareReporter
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Result[]
     */
    private $results = array();
    private $orderBy;
    private $rules = array();

    public function init($orderBy = 'url')
    {
        $this->orderBy = $orderBy;
    }

    public function setConfig(Configuration $config)
    {
        $this->rules = $config->getRules();
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
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

    /**
     *
     */
    private function renderRuleOutput()
    {
        $this->output->writeln("\n\n <comment>Rules and Violations:</comment> \n");

        foreach ($this->rules as $ruleKey => $rule) {
            $info = Init::getInitInformationByClass($rule);

            $failedUrls = array();

            foreach ($this->results as $result) {
                if ($result->isFailure()) {
                    $messages = $result->getMessages();
                    foreach ($messages as $key => $message) {
                        if ($key === $ruleKey) {
                            $failedUrls[] = (string) $result->getUrl();
                        }
                    }
                }
            }

            if (count($failedUrls) > 0) {
                $this->output->writeln('  <error> ' . get_class($rule) . ' </error>');
            } else {
                $this->output->writeln('  <info> ' . get_class($rule) . ' </info>');
            }

            $this->output->writeln('   ' . str_replace("\n", "\n   ", $info['documentation']));

            $this->output->writeln('');

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
                $this->output->writeln('   <info> ' . $result->getUrl() . ' </info> all tests passed');
            }
        }

        $this->output->writeln("\n <comment>Failed tests:</comment> \n");

        foreach ($this->results as $result) {
            if ($result->isFailure()) {
                $this->output->writeln('   <error> ' . $result->getUrl() . ' </error> coming from ' . $result->getParent());
                foreach ($result->getMessages() as $ruleName => $message) {
                    $this->output->writeln('    - ' . $message . " [rule: $ruleName]");
                }
                $this->output->writeln('');
            }
        }
    }
}

<?php

namespace whm\Smoke\Cli\Command;

use phmLabs\Components\Annovent\Dispatcher;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;

class ScanCommand extends SmokeCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('url', InputArgument::REQUIRED, 'the url to start with'),
                new InputOption('parallel_requests', 'p', InputOption::VALUE_OPTIONAL, 'number of parallel requests.', 10),
                new InputOption('num_urls', 'u', InputOption::VALUE_OPTIONAL, 'number of urls to be checked', 20),
            ])
            ->setDescription('analyses a website')
            ->setHelp('The <info>analyse</info> command runs a cache test.')
            ->setName('analyse');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($output);

        $this->writeSmokeCredentials($input->getArgument('url'));

        $this->initConfiguration(
            $input->getOption('num_urls'),
            $input->getOption('parallel_requests'),
            new Uri($input->getArgument('url')),
            $this->eventDispatcher);

        return $this->scan();
    }

    /**
     * Initializes the configuration.
     *
     * @return Configuration
     */
    private function initConfiguration($num_urls, $parallel_requests, Uri $uri, Dispatcher $dispatcher)
    {
        $configArray = Yaml::parse(file_get_contents(__DIR__ . '/../../settings/analyze.yml'));

        $config = new Configuration($uri, $dispatcher, $configArray);

        $crawler = $config->getExtension('_ResponseRetriever')->getRetriever();
        $crawler->setStartPage($uri);

        if ($num_urls) {
            $config->getExtension('_SmokeStop')->getStrategy('_CountStop')->init($num_urls);
            $config->getExtension('_ProgressBar')->setMax($num_urls);
        }

        if ($parallel_requests) {
            $config->setParallelRequestCount($parallel_requests);
        }

        $this->config = $config;
    }
}

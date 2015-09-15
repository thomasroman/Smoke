<?php

namespace whm\Smoke\Cli\Command;

use phmLabs\Components\Annovent\Dispatcher;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Html\Uri;
use whm\Smoke\Config\Configuration;

class ScanCommand extends SmokeCommand
{
    const CONFIG_FILE = 'analyze.yml';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('url', InputArgument::REQUIRED, 'the url to start with'),
                new InputOption('num_urls', 'u', InputOption::VALUE_OPTIONAL, 'number of urls to be checked', 20),
                new InputOption('run_level', 'l', InputOption::VALUE_OPTIONAL, 'limit the rules that are called', 10),
            ])
            ->setDescription('analyses a website')
            ->setHelp('The <info>analyse</info> command runs a cache test.')
            ->setName('analyse');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output, $input->getArgument('url'));

        $this->initConfiguration(
            $input->getOption('num_urls'),
            $input->getOption('run_level'),
            new Uri($input->getArgument('url')),
            $this->eventDispatcher);

        return $this->scan();
    }

    /**
     * Initializes the configuration.
     *
     * @return Configuration
     */
    private function initConfiguration($num_urls, $run_level, Uri $uri, Dispatcher $dispatcher)
    {
        $configArray = $this->getConfigArray(__DIR__ . '/../../settings/' . self::CONFIG_FILE);

        $config = new Configuration($uri, $dispatcher, $configArray);

        $crawler = $config->getExtension('_ResponseRetriever')->getRetriever();
        $crawler->setStartPage($uri);

        if ($num_urls) {
            $config->getExtension('_SmokeStop')->getStrategy('_CountStop')->init($num_urls);
            $config->getExtension('_ProgressBar')->setMax($num_urls);
        }

        if ($run_level) {
            $config->getExtension('_SmokeRunLevel')->setRunLevel($run_level);
        }

        $this->config = $config;
    }
}

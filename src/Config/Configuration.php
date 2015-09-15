<?php

namespace whm\Smoke\Config;

use phmLabs\Components\Annovent\Dispatcher;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Yaml\Yaml;
use whm\Html\Uri;
use whm\Smoke\Rules\Rule;

class Configuration
{
    const DEFAULT_SETTINGS = 'analyze.yml';

    private $startUri;

    private $rules = [];

    private $configArray;

    private $eventDispatcher;

    private $extensions = array();

    private $runLevels = array();

    public function __construct(Uri $uri, Dispatcher $eventDispatcher, array $configArray, array $defaultSettings = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        Init::registerGlobalParameter('_configuration', $this);

        $this->initConfigArray($configArray, $defaultSettings);

        if (array_key_exists('extensions', $this->configArray)) {
            $this->addListener($this->configArray['extensions']);
        }

        if (!array_key_exists('rules', $this->configArray)) {
            $this->configArray['rules'] = [];
        }

        $this->startUri = $uri;
        $this->initRules($this->configArray['rules']);
    }

    private function initConfigArray(array $configArray, array $defaultSettings = null)
    {
        if ($defaultSettings === null) {
            $defaultSettings = Yaml::parse(file_get_contents(__DIR__ . '/../settings/' . self::DEFAULT_SETTINGS));
        }

        if (count($configArray) === 0) {
            $configArray = $defaultSettings;
        }

        if (array_key_exists('options', $configArray)) {
            if (array_key_exists('extendDefault', $configArray['options'])) {
                if ($configArray['options']['extendDefault'] === true) {
                    $configArray = array_replace_recursive($defaultSettings, $configArray);
                }
            }
        }

        $this->configArray = $configArray;
    }

    private function addListener(array $listenerArray)
    {
        foreach ($listenerArray as $key => $listenerConfig) {
            $extension = Init::initialize($listenerConfig);
            $this->extensions[$key] = $extension;
            $this->eventDispatcher->connectListener($extension);
        }
    }

    /**
     * @return Uri
     */
    public function getStartUri()
    {
        return $this->startUri;
    }

    /**
     * This function initializes all the rules and sets the log level.
     *
     * @param array $rulesArray
     */
    private function initRules(array $rulesArray)
    {
        foreach ($rulesArray as $key => $ruleElement) {
            if (array_key_exists('logLevel', $ruleElement)) {
                $this->runLevels[$key] = (int) $ruleElement['logLevel'];
            } else {
                $this->runLevels[$key] = 0;
            }
            $this->rules[$key] = Init::initialize($ruleElement);
        }
    }

    /**
     * Returns the log level of a given rule.
     *
     * @param string $key
     *
     * @return int
     */
    public function getRuleRunLevel($key)
    {
        return $this->runLevels[$key];
    }

    /**
     * @return Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    public function hasSection($section)
    {
        return (array_key_exists($section, $this->configArray));
    }

    /**
     * @param $section
     *
     * @return array
     */
    public function getSection($section)
    {
        if ($this->hasSection($section)) {
            return $this->configArray[$section];
        } else {
            throw new \RuntimeException('The section (' . $section . ') you are trying to access does not exist.');
        }
    }

    public function getExtension($name)
    {
        if (array_key_exists($name, $this->extensions)) {
            return $this->extensions[$name];
        } else {
            throw new \RuntimeException('The extension ("' . $name . '") you are trying to access does not exist. Registered extensions are: ' . implode(' ,', array_keys($this->extensions)) . '.');
        }
    }

    public function addExtension($name, $extension)
    {
        $this->extensions[$name] = $extension;
        $this->eventDispatcher->connectListener($extension);
    }

    /**
     * Returns the config array.
     *
     * @return array
     */
    public function getConfigArray()
    {
        return $this->configArray;
    }
}

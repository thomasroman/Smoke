<?php

namespace whm\Smoke\Config;

use whm\Html\Uri;
use phmLabs\Components\Annovent\Dispatcher;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Yaml\Yaml;
use whm\Smoke\Rules\Rule;

class Configuration
{
    const DEFAULT_SETTINGS = 'analyze.yml';

    private $startUri;

    private $containerSize;

    private $parallelRequestCount;

    private $rules = [];

    private $configArray;

    private $eventDispatcher;

    private $extensions = array();

    public function __construct(Uri $uri, Dispatcher $eventDispatcher, array $configArray, array $defaultSettings = null)
    {
        $this->eventDispatcher = $eventDispatcher;

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

        if (array_key_exists('extensions', $configArray)) {
            $this->addListener($configArray['extensions']);
        }

        if (!array_key_exists('rules', $configArray)) {
            $configArray['rules'] = [];
        }

        $this->configArray = $configArray;

        $this->startUri = $uri;
        $this->rules = Init::initializeAll($configArray['rules']);
    }

    private function addListener(array $listenerArray)
    {
        foreach ($listenerArray as $key => $listenerConfig) {
            $extension = Init::initialize($listenerConfig);
            $this->extensions[$key] = $extension;
            $this->eventDispatcher->connectListener($extension);
        }
    }

    public static function getDefaultConfig(Uri $uri)
    {
        $defaultSettings = Yaml::parse(file_get_contents(__DIR__ . '/../settings/' . self::DEFAULT_SETTINGS));

        return new self($uri, $defaultSettings);
    }

    /**
     * @return Uri
     */
    public function getStartUri()
    {
        return $this->startUri;
    }

    public function setContainerSize($size)
    {
        $this->containerSize = $size;
    }

    public function getContainerSize()
    {
        return $this->containerSize;
    }

    public function setParallelRequestCount($count)
    {
        $this->parallelRequestCount = $count;
    }

    public function getParallelRequestCount()
    {
        return $this->parallelRequestCount;
    }

    /**
     * @return Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    public function isUriAllowed(Uri $uri, $currentUri)
    {
        if (!$this->scanForeignDomains()) {
            $tlds = explode('.', $uri->getHost());

            if (count($tlds) < 2) {
                return false;
            }

            $currentTld = $tlds[count($tlds) - 2] . '.' . $tlds[count($tlds) - 1];

            $tlds = explode('.', $this->startUri->getHost());
            $startTld = $tlds[count($tlds) - 2] . '.' . $tlds[count($tlds) - 1];

            if ($currentTld !== $startTld) {
                return false;
            }
        }

        return false;
    }

    public function hasSection($section)
    {
        return (array_key_exists($section, $this->configArray));
    }

    public function getSection($section)
    {
        return $this->configArray[$section];
    }

    public function getExtension($name)
    {
        return $this->extensions[$name];
    }

    public function addExtension($name, $extension)
    {
        $this->extensions[$name] = $extension;
        $this->eventDispatcher->connectListener($extension);
    }
}

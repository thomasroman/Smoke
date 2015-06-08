<?php

namespace whm\Smoke\Config;

use Phly\Http\Uri;
use phmLabs\Components\Annovent\Dispatcher;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Yaml\Yaml;
use whm\Smoke\Rules\Rule;

class Configuration
{
    const DEFAULT_SETTINGS = 'default.yml';

    private $blacklist;
    private $whitelist;

    private $scanForeignDomains = false;

    private $startUri;

    private $containerSize;

    private $parallelRequestCount;

    private $rules = [];

    private $configArray;

    private $eventDispatcher;

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
            if (array_key_exists('scanForeignDomains', $configArray['options'])) {
                $this->scanForeignDomains = $configArray['options']['scanForeignDomains'];
            }
        }

        if (array_key_exists('extensions', $configArray)) {
            $this->addListener($configArray['extensions']);
        }

        if (array_key_exists('blacklist', $configArray)) {
            $this->blacklist = $configArray['blacklist'];
        } else {
            $this->blacklist = [];
        }

        if (array_key_exists('whitelist', $configArray)) {
            $this->whitelist = $configArray['whitelist'];
        } else {
            $this->whitelist = ['^^'];
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
        $listeners = Init::initializeAll($listenerArray);
        foreach ($listeners as $listener) {
            $this->eventDispatcher->connectListener($listener);
        }
    }

    public static function getDefaultConfig(Uri $uri)
    {
        $defaultSettings = Yaml::parse(file_get_contents(__DIR__ . '/../settings/' . self::DEFAULT_SETTINGS));

        return new self($uri, $defaultSettings);
    }

    public function getStartUri()
    {
        return $this->startUri;
    }

    public function enableForeignDomainScan()
    {
        $this->scanForeignDomains = true;
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

    public function getBlacklist()
    {
        return $this->blacklist;
    }

    public function getWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * @return Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    public function scanForeignDomains()
    {
        return $this->scanForeignDomains;
    }

    public function isUriAllowed(Uri $uri)
    {
        if (!$this->scanForeignDomains()) {
            $tlds = explode('.', $uri->getHost());

            $currentTld = $tlds[count($tlds) - 2] . '.' . $tlds[count($tlds) - 1];

            $tlds = explode('.', $this->startUri->getHost());
            $startTld = $tlds[count($tlds) - 2] . '.' . $tlds[count($tlds) - 1];

            if ($currentTld !== $startTld) {
                return false;
            }
        }

        foreach ($this->whitelist as $whitelist) {
            if (preg_match($whitelist, (string) $uri)) {
                foreach ($this->blacklist as $blacklist) {
                    if (preg_match($blacklist, (string) $uri)) {
                        return false;
                    }
                }

                return true;
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
}

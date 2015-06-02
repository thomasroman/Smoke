<?php

namespace whm\Smoke\Config;

use Phly\Http\Uri;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Yaml\Yaml;
use whm\Smoke\Rules\Rule;

class Configuration
{
    private $blacklist;
    private $whitelist;

    private $scanForeignDomains = false;

    private $startUri;

    private $containerSize;

    private $parallelRequestCount;

    private $rules = [];

    private $reporter;

    const DEFAULT_SETTINGS = 'default.yml';

    public function __construct(Uri $uri, array $configArray, array $defaultSettings = null)
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
            if (array_key_exists('scanForeignDomains', $configArray['options'])) {
                $this->scanForeignDomains = $configArray['options']['scanForeignDomains'];
            }
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

        $this->startUri = $uri;

        $this->reporter = Init::initialize($configArray['reporter']);
        $this->rules = Init::initializeAll($configArray['rules']);
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
            $tlds = explode(".", $uri->getHost());
            $currentTld = array_pop($tlds);

            $tlds = explode(".", $this->startUri->getHost());
            $startTld = array_pop($tlds);

            if ($currentTld !== $startTld) {
                return false;
            }
        }

        foreach ($this->whitelist as $whitelist) {
            if (preg_match($whitelist, (string)$uri)) {
                foreach ($this->blacklist as $blacklist) {
                    if (preg_match($blacklist, (string)$uri)) {
                        return false;
                    }
                }

                return true;
            }
        }

        return false;
    }

    public function getReporter()
    {
        return $this->reporter;
    }
}

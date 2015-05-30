<?php

namespace whm\Smoke\Scanner;

use Phly\Http\Uri;

class PageContainer
{
    private $currentElements = [];
    private $allElements = [];

    private $maxSize;

    public function __construct($maxSize = 100)
    {
        $this->maxSize = $maxSize;
    }

    public function getMaxSize()
    {
        return $this->maxSize;
    }

    public function getAllElements()
    {
        return $this->allElements;
    }

    public function push(Uri $uri, Uri $parentUri)
    {
        $uriString = (string)$uri;

        if (count($this->allElements) < $this->maxSize) {
            if (!array_key_exists($uriString, $this->allElements)) {
                $this->allElements[$uriString] = (string)$parentUri;
                array_unshift($this->currentElements, $uri);
            }
        }
    }

    public function pop($count = 1)
    {
        $elements = [];

        for ($i = 0; $i < $count; ++$i) {
            $element = array_pop($this->currentElements);
            if (!is_null($element)) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    public function getParent(Uri $uri)
    {
        return isset($this->allElements[(string)$uri]) ? $this->allElements[(string)$uri] : null;
    }
}

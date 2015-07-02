<?php

namespace whm\Smoke\Http;

class Response extends \Ivory\HttpAdapter\Message\Response
{
    private $contents;

    public function getStatus()
    {
        return $this->getStatusCode();
    }

    public function getContentType()
    {
        $exploded = explode(';', $this->hasHeader('Content-Type') ? $this->getHeader('Content-Type')[0] : null);

        return array_shift($exploded);
    }

    public function getUri()
    {
        return $this->getParameter('request')->getUri();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->getParameter('request');
    }

    public function getDuration()
    {
        return $this->getParameter('duration');
    }

    public function getBody()
    {
        if (!$this->contents) {
            $contents = parent::getBody()->getContents();

            if (false !== $content = @gzdecode($contents)) {
                $contents = $content;
            }

            $this->contents = $contents;
        }

        return $this->contents;
    }
}

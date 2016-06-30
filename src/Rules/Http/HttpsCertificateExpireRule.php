<?php

namespace whm\Smoke\Rules\Http;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if a given https certificate expire in a few days.
 */
class HttpsCertificateExpireRule implements Rule
{
    private $expireWarningTime;

    /**
     * @param int $expireWarningTime in days
     */
    public function init($expireWarningTime = 14)
    {
        $this->expireWarningTime = $expireWarningTime;
    }

    public function validate(Response $response)
    {
        if ('https' === $response->getUri()->getScheme()) {
            $sslOptions = stream_context_create(array('ssl' => array('capture_peer_cert' => true)));

            $request = stream_socket_client(
                'ssl://' . $response->getUri()->getHost() . ':443',
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $sslOptions
            );

            $content = stream_context_get_params($request);
            $certinfo = openssl_x509_parse($content['options']['ssl']['peer_certificate']);

            $validFrom = date('d.m.Y H:i:s', $certinfo['validFrom_time_t']);
            $validTo = date('d.m.Y H:i:s', $certinfo['validTo_time_t']);

            if ($certinfo['validFrom_time_t'] > time() || $certinfo['validTo_time_t'] < time()) {
                $errorMessage = 'Certificate is expired. [' . $validFrom . ' - ' . $validTo . ']';
                throw new ValidationFailedException($errorMessage);
            } elseif ($certinfo['validTo_time_t'] < strtotime('+' . $this->expireWarningTime . 'days')) {
                $errorMessage = 'Certificate warning, expires in less than ' . $this->expireWarningTime . ' days. Certificate expires at: ' . $validTo;
                throw new ValidationFailedException($errorMessage);
            }
        }
    }
}

<?php

namespace whm\Smoke\Rules\Http;

use whm\Smoke\Rules\CheckResult;

/**
 * This rule checks if a given https certificate expire in a few days.
 */
class HttpsCertificateExpireRule extends HttpsRule
{
    private $expireWarningTime;

    /**
     * @param int $expireWarningTime in days
     */
    public function init($expireWarningTime = 14)
    {
        $this->expireWarningTime = $expireWarningTime;
    }

    protected function doValidate($certInfo)
    {
        $validFrom = date('d.m.Y H:i:s', $certInfo['validFrom_time_t']);
        $validTo = date('d.m.Y H:i:s', $certInfo['validTo_time_t']);

        if ($certInfo['validFrom_time_t'] > time() || $certInfo['validTo_time_t'] < time()) {
            $errorMessage = 'Certificate is expired. [' . $validFrom . ' - ' . $validTo . ']';

            return new CheckResult(CheckResult::STATUS_FAILURE, $errorMessage);
        } elseif ($certInfo['validTo_time_t'] < strtotime('+' . $this->expireWarningTime . 'days')) {
            $errorMessage = 'Certificate warning, expires in less than ' . $this->expireWarningTime . ' days. Certificate expires at: ' . $validTo;

            return new CheckResult(CheckResult::STATUS_FAILURE, $errorMessage, 0);
        }

        return new CheckResult(CheckResult::STATUS_SUCCESS, 'The certificate does not expire within the next ' . $this->expireWarningTime . ' days. Expire date: ' . $validTo . '.');
    }
}

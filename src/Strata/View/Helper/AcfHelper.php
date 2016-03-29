<?php
namespace IP\Code\Strata\View\Helper;

use Strata\Strata;
use Strata\View\Helper\Helper as StrataHelper;
use IP\Code\Common\AcfCacheTrait;

class AcfHelper extends StrataHelper {
    use AcfCacheTrait;

    protected function log($call)
    {
        $context = "unknown context @ unknown line";
        foreach (debug_backtrace() as $idx => $file) {
            if ($file['file'] != __FILE__) {
                $context = sprintf("%s @ %s", $file['file'], $file['line']);
                break;
            }
        }

        $partialFilePath = defined('ABSPATH') ? str_replace(dirname(dirname(ABSPATH)), "", $context) : $context;
        Strata::app()->getLogger("IPLogger")->log($partialFilePath . ': ' . $call, "<yellow>IP:AcfHelper</yellow>");
    }
}

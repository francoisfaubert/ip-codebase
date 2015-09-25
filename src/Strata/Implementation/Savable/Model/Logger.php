<?php
namespace IP\Code\Strata\Implementation\Savable\Model;

use Strata\Logger\Logger as StrataLogger;

class Logger extends StrataLogger {

    public $color =  "\e[0;35m";

    private $executionStart = 0;

    public function logQueryStart()
    {
        $this->executionStart = microtime(true);
    }

    public function logQueryCompletion($sql)
    {
        $executionTime = microtime(true) - $this->executionStart;
        $timer = sprintf(" (Done in %s seconds)", round($executionTime, 4));
        $oneLine = preg_replace('/\s+/', ' ', trim($sql));

        $label = "[IP-Codebase:Savable]";
        $this->log($oneLine . $timer, $label);
    }
}

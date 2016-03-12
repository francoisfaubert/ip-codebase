<?php
namespace IP\Code\Strata\View\Helper;

use Strata\Strata;

class AcfHelper extends \Strata\View\Helper\Helper {

    private $cache = array();
    private $defaultId = null;

    public function refresh($id = null)
    {
        $id = $this->proofCurrentId($id);
        $this->getFields($id);
    }

    public function get($field, $id = null)
    {
        $id = $this->proofCurrentId($id);

        if (!$this->hasCached($id)) {
            $this->getFields($id);
        }

        // We could throw an error, but I think
        // views would be broken too often...
        if ($this->check($field, $id)) {
            return $this->cache[$this->getCacheKey($id)][$field];
        }
    }

    public function check($field, $id = null)
    {
        return $this->isCachedValue($field, $this->proofCurrentId($id));
    }

    public function hasCached($id = null)
    {
        $key = $this->getCacheKey($this->proofCurrentId($id));
        return array_key_exists($key, $this->cache);
    }

    public function isEmpty($field, $id = null)
    {
        $value = $this->get($field, $id);
        return empty($value);
    }

    public function isArray($field, $id = null)
    {
        $value = $this->get($field, $id);
        return is_array($value);
    }

    public function isNull($field, $id = null)
    {
        $value = $this->get($field, $id);
        return is_null($value);
    }

    private function getCacheKey($id)
    {
        return "cache-" . $id;
    }

    private function proofCurrentId($id = null)
    {
        if (is_null($id)) {
            // Only query once for default post id.
            if (is_null($this->defaultId)) {
                $this->defaultId = get_the_ID();
            }
            return $this->defaultId;
        }

        return (int)$id;
    }

    private function isCachedValue($field, $id)
    {
        return $this->hasCached($id) && array_key_exists($field, $this->cache[$this->getCacheKey($id)]);
    }

    private function getFields($id)
    {
        $startedAt = microtime(true);
        $this->cache[$this->getCacheKey($id)] = (array)get_fields($id);
        $completion = microtime(true) - $startedAt;

        $validatedId = (!$id) ? "current global ID" : "#" . $id;

        $this->log(sprintf("Loading data for <info>%s</info>. (Done in %s seconds)", $validatedId, round($completion, 4)));
    }

    private function log($call)
    {
        $context = "unknown context @ unknown line";
        foreach (debug_backtrace() as $idx => $file) {
            if ($file['file'] != __FILE__) {
                $context = sprintf("%s @ %s", $file['file'], $file['line']);
                break;
            }
        }

        $partialFilePath = defined('ABSPATH') ? str_replace(dirname(dirname(ABSPATH)), "", $context) : $context;
        Strata::config("loggers.IPLogger")->log($partialFilePath . ': ' . $call, "<yellow>IP:AcfHelper</yellow>");
    }
}

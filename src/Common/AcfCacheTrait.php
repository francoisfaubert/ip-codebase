<?php

namespace IP\Code\Common;


trait AcfCacheTrait {

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
        $id = $this->proofCurrentId($id);

        if (!$this->hasCached($id)) {
            $this->getFields($id);
        }

        return $this->isCachedValue($field, $id);
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

            return (int)$this->defaultId;
        }

        return (int)$id;
    }

    private function isCachedValue($field, $id)
    {
        $key = $this->getCacheKey($id);
        return $this->hasCached($id) && array_key_exists($field, $this->cache[$key]);
    }

    private function getFields($id)
    {
        if (!function_exists("get_fields")) {
             $this->log("ACF plugin is not enabled.");
            return;
        }

        $startedAt = microtime(true);
        $this->cache[$this->getCacheKey($id)] = (array)get_fields($id);
        $completion = microtime(true) - $startedAt;

        $validatedId = (!$id) ? "current global ID" : "#" . $id;

        $this->log(sprintf("Loading data for <info>%s</info>. (Done in %s seconds)", $validatedId, round($completion, 4)));
    }

    protected function log($call)
    {
        if (class_exists("Strata\Strata")) {
            Strata::app()->log($call);
        }
    }

}

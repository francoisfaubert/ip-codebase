<?php

namespace IP\Code\Common;

use Strata\Utility\Inflector;

trait SlugTrait {

    public function toSlug($string)
    {
        return strtolower(Inflector::slug($string, "-"));
    }

}

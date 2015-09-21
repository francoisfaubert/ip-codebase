<?php
namespace IP\Code\Common;

trait SlugTrait {

    public function toSlug($string)
    {
        return strtolower(preg_replace("/\W/i", "", $string));
    }

}

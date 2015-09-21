<?php
namespace IP\Code\View\Helper;

trait SlugTrait {

    public function toSlug($string)
    {
        return strtolower(preg_replace("/\W/i", "", $string));
    }

}

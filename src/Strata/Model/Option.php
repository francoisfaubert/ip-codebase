<?php
namespace IP\Code\Strata\Model;

use IP\Code\Strata\Model\Entity\OptionEntity;
use object;
use Strata\Utility\Hash;

/**
 * Class that eases pull out option values from Wordpress' option table.
 */
class Option extends \Strata\Model\Model {

    public static function load($optionKey)
    {
        $option = get_option($optionKey, array());
        $data = maybe_unserialize($option, true);

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return new OptionEntity((object)$data);
    }

    public static function save($optionKey, $values)
    {
        $normed = Hash::normalize($values);

        $data = array_key_exists('optionentity', $normed) ?
            $normed['optionentity'] :
            $normed;

        return update_option($optionKey, json_encode($data), false);
    }
}

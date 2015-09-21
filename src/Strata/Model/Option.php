<?php
namespace IP\Code\Strata\Model;

use IP\Code\Strata\Model\Entity\OptionEntity;
use object;

use Strata\Utility\Hash;

/**
 * Class that eases pull out option values from Wordpress' option table.
 */
class Option extends AppModel {

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
        return update_option($optionKey, json_encode($normed['optionentity']), false);
    }
}

<?php
namespace IP\Code\Strata\Model\Entity;

class OptionEntity extends \Strata\Model\CustomPostType\ModelEntity {

    // Fake the attribute registration process, expect everything to be
    // a valid field.
    public function isSupportedAttribute($attr)
    {
        return true;
    }

}

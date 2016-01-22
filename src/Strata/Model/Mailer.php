<?php
namespace IP\Code\Strata\Model;

use Strata\Model\Mailer as StrataMailer;
use Exception;


/**
 * This class adds to the default Strata Mailer class by allowing
 * the object to load up website options we would have saved. This allows
 * us to dump the different email destinations in wp_option fields.
 */
abstract class Mailer extends StrataMailer
{
    const EMAIL_SEPARATOR = ";";

    abstract protected function loadOptions();

    protected $options = array();

    public function __construct()
    {
        $this->loadOptions();
    }

    protected function getOptionKey($optionKey)
    {
        $optionValue = $this->options->{$optionKey};

        if (!empty($optionValue)) {
            return $optionValue;
        }
    }
}

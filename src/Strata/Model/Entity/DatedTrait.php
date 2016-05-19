<?php
namespace IP\Code\Strata\Model\Entity;

use Exception;

trait DatedTrait {

    public function getAcfDateKey()
    {
        return "date";
    }

    public function getAcfDate()
    {
        return get_field($this->getAcfDateKey(), $this->ID);
    }

    public function getFormattedDate($format = "%h %e")
    {
        return strftime($format, $this->getAcfDateTimestamp());
    }

    public function getAcfDateTimestamp()
    {
        $date = $this->getAcfDate();
        if ($date != '') {
            // if the format is 2015/09/09
            // $activityDate = explode("/", $this->getAcfDate());
            // return  mktime(0, 0, 0, $activityDate[1], $activityDate[0], $activityDate[2]);

            $activityDate = preg_match('/(\d{4})(\d{2})(\d{2})/', $this->getAcfDate(), $matches);

            if (count($matches) !== 4) {
                throw new Exception(sprintf("The ACF field '%s' on '%s' needs to be explicitly formatted as 'Ymd'.", $this->getAcfDateKey(), get_class()));
            }

            return  mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
        }
    }

    public function getNbDaysBeforeStart()
    {
        if ($this->isUpcoming()) {
            $remain = $this->getAcfDateTimestamp() - time();
            return ceil($remain / DAY_IN_SECONDS);
        }

        return 0;
    }

    public function isUpcoming()
    {
        return $this->getAcfDateTimestamp() > time();
    }

}

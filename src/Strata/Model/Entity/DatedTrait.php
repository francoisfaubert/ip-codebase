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

    public function getFormattedDate()
    {
        return date("M dS", $this->getAcfDateTimestamp());
    }

    public function getAcfDateTimestamp()
    {
        $date = $this->getAcfDate();
        if ($date != '') {
            // if the format is 2015/09/09
            // $activityDate = explode("/", $this->getAcfDate());
            // return  mktime(0, 0, 0, $activityDate[1], $activityDate[0], $activityDate[2]);

            // if the format is 20150916
            $activityDate = preg_match('/(\d{4})(\d{2})(\d{2})/', $this->getAcfDate(), $matches);
            return  mktime(0, 0, 0, $activityDate[2], $activityDate[3], $activityDate[1]);
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

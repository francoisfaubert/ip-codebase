<?php
namespace IP\Code\Strata\Model;

trait DateParserTrait {

    public static function formatDate($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }
        return date("Ymd", $timestamp);
    }

    public static function acfTimeIsUpcoming($activityDate)
    {
        // if the format is 2015/09/09
        // $activityDate = explode("/", $activityDate);
        // return  mktime(0, 0, 0, $activityDate[1], $activityDate[0], $activityDate[2]) > time();

        // if the format is 20150916
        $activityDate = preg_match('/(\d{4})(\d{2})(\d{2})/', $activityDate, $matches);
        return  mktime(0, 0, 0, $activityDate[2], $activityDate[3], $activityDate[1]) > time();
    }

    public function getAcfDateKey()
    {
        return "acf_date";
    }

    /**
     * Date format must be the same as the ACF. Currently it's Ymd
     * @param  string $date
     * @return array
     */
    public function byDate($date)
    {
        return $this->where('meta_query', array(array(
            'key' => $this->getAcfDateKey(),
            'value' => $date,
            'compare' => '==',
            'type'      => 'DATE'
        )));
    }

    public function byDates(array $dates)
    {
        $queries = array(
            'relation' => 'OR',
        );

        foreach ($dates as $date) {
            $queries[] = array(
                'key' => $this->getAcfDateKey(),
                'value' => $date,
                'compare' => '==',
                'type'      => 'DATE'
            );
        }

        return $this->where('meta_query', $queries);
    }

    public function byRange($dateStart, $dateEnd)
    {
        $queries = array(
            'relation' => 'AND',
            array(
                'key' => $this->getAcfDateKey(),
                'value' => $dateStart,
                'compare' => '>=',
                'type'      => 'DATE'
            ),
            array(
                'key' => $this->getAcfDateKey(),
                'value' => $dateEnd,
                'compare' => '<=',
                'type'      => 'DATE'
            )
        );

        return $this->where('meta_query', $queries);
    }

    public function inTheFuture($compare = '>')
    {
        return $this->where('meta_query', array(
            'key' => $this->getAcfDateKey(),
            'value' => date('Ymd'),
            'compare' => $compare,
            'type'      => 'DATE'
        ));
    }

    public function inThePast($compare = '<')
    {
        return $this->where('meta_query', array(
            'key' => $this->getAcfDateKey(),
            'value' => date('Ymd'),
            'compare' => $compare,
            'type'      => 'DATE'
        ));
    }

    public function inTheFutureOrWithNoDates()
    {
        $this->orWhere('meta_query', array(
            'key' => $this->getAcfDateKey(),
            'value' => "",
            'compare' => '=',
        ));
        $this->orWhere('meta_query', array(
            'key' => $this->getAcfDateKey(),
            'value' => $this->formatDate(),
            'compare' => '>=',
            'type'      => 'DATE'
        ));

        return $this;
    }

    public function findFuture()
    {
        return $this->inTheFuture()->fetch();
    }

    public function findPast()
    {
        return $this->inThePast()->fetch();
    }

    public function todayIsMonday($time = null)
    {
        return (int)date('N', $time) === 1;
    }

    public function lastMonday($time = null)
    {
        $workingTime = is_null($time) ? time() : $time;
        if ($this->todayIsMonday($workingTime)) {
            return strtotime("today", $workingTime);
        }

        return strtotime("last monday", $workingTime);
    }

    public function nextMonday($time = null)
    {
        $workingTime = is_null($time) ? time() : $time;
        return strtotime("next monday", $workingTime);
    }

    public function firstDayOfMonth($time = null)
    {
        $workingTime = is_null($time) ? time() : $time;
        return strtotime("first day of " . date('F Y', $workingTime));
    }

    public function lastDayOfMonth($time = null)
    {
        $workingTime = is_null($time) ? time() : $time;
        return strtotime("last day of " . date('F Y', $workingTime));
    }

    public function weekdayStartOfMonth($time = null)
    {
        $firstDay = $this->firstDayOfMonth($time);
        $actualWeekDay = date('N', $firstDay);

        return $firstDay - ($actualWeekDay * DAY_IN_SECONDS);
    }

    public function weekdayEndOfMonth($time = null)
    {
        $lastDay = $this->lastDayOfMonth($time);
        $actualWeekDay = date('N', $lastDay);

        return $lastDay + ((7 - $actualWeekDay) * DAY_IN_SECONDS);
    }

}

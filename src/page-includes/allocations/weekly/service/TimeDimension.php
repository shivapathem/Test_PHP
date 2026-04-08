<?php
/*
 * Created on Tue Nov 23 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name: Class TimeDimension
 * Description: A value object for DB Table
 *
 * Copyright (c) 2021 BBC
 */


include_once __DIR__ . "/ArrayableTrait.php";

class TimeDimension 
{
    use ArrayableTrait;

    public $ID;

    public $AllocateInstanceID = 0;

    public $ixDateKey;

    public $dDateTime;

    public $ixDayInWeek;

    public $ixDayInMonth;

    public $ixDayInYear;

    public $ixWeekInYear;

    public $ixMonthInYear;

    public $ixQuarterInYear;

    public $ixYear;

    public $ixYearWeek;

    public $ixLeaveYear;

    public $ixWeekFromStart;

    public $sDayName;

    public $sMonthName;

    public $fPayRollDay;

    public $fWeekDayFlag;

    public $fHolidayFlag;

    public $fHolidayPayment;

    public $ixHolidayPayment;

    public $sEvent;

    public $history;

    
    public function __construct($data = [])
    {
        if (!empty($data)){
            $this->fromArray($data);
        }
    }

}
<?php

function getDateTimeInEuropeTimezone($onlyDate = 0, $onlyTime = 0, $formatStr = '', $incDate = ''){
	$date = new DateTime('now', new DateTimeZone('UTC'));
	if($incDate != ''){
		$date = new DateTime($incDate, new DateTimeZone('UTC'));
	}
    $date->setTimezone(new DateTimeZone('Europe/London'));

    if($onlyDate == 1){
    	if($formatStr == ''){
    		return $date->format('d/m/Y');
    	} else {
    		return $date->format($formatStr);
    	}
    } else if($onlyTime == 1){
    	return $date->format('H:i');
    } else {
    	return '';
    }
}


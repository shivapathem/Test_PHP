<?php
/*
 * Created on Wed 01 Dec 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name:  Class RequestService
 * Description: A service to generate BBC specific requests 
 *
 * Copyright (c) 2021 BBC
 */

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
require_once __DIR__ . "/TimeDimensionService.php";

class RequestService
{
    public static ?Request $request = null;

    public static function prepareRequestDates(Request $request): Request
    {
        // Check if already prepared
        if ($request->get('startDate') && $request->get('endDate')) {
            return $request;
        }

        $weekNum = null;
        $weekNumberInput = $request->get('weekNumber', '');
		if($request->get('ajaxLoadParams'))
		{
			$ajaxLoadParams = $request->get('ajaxLoadParams');
			$ajaxLoadParams = json_decode($ajaxLoadParams, true);
			$curWeekNumber = $ajaxLoadParams['weekNumber'];
			$curWeekNumber = substr($curWeekNumber, 0, 2);
			if(strlen($weekNumberInput) == 2)
			{
				if($weekNumberInput > 52)
				{
					$weekNumberInput = $curWeekNumber;
				}
			}else
			{
				$weekNumberInputW = substr($weekNumberInput, 0, 2);
				$weekNumberInputY = substr($weekNumberInput, 2, 5);
				if($weekNumberInputW > 52)
				{
					$weekNumberInput = $curWeekNumber . $weekNumberInputY;
				}
			}
		}

        if ($weekNumberInput !== '' && !str_contains($weekNumberInput, '/')) {
            $weekNum = str_pad($weekNumberInput, 2, '0', STR_PAD_LEFT) . '/' . date('Y');
            $weekNum2 = str_pad($request->get('weekNumber'), 2, '0', STR_PAD_LEFT) . '/' . date('Y');
        } elseif ($weekNumberInput !== '' && str_contains($weekNumberInput, '/')) {
            $weekNum = $weekNumberInput;
            $weekNum2 = $request->get('weekNumber');

            [$week, $year] = explode('/', $weekNum);
            [$week2, $year2] = explode('/', $weekNum2);

            if (strlen($year) === 2) {
                $year = "20$year";
            }
            if (strlen($year2) === 2) {
                $year2 = "20$year2";
            }
            $week = str_pad($week, 2, '0', STR_PAD_LEFT);
            $weekNum = sprintf('%s/%s', $week, $year);

            $week2 = str_pad($week2, 2, '0', STR_PAD_LEFT);
            $weekNum2 = sprintf('%s/%s', $week2, $year2);
        }

        if (($weekNum !== null) && ($weekNum !== '0') && $weekNum == $weekNum2) {
            $request->request->set('weekNumber', $weekNum);
        }

        $timedimension = new TimeDimensionService();

        if (!empty($date = $request->get('date', ''))) {
            $dateStartWeekNum = $timedimension->findByDateWithoutCarbon(date('Y-m-d', strtotime($date)))->ixYearWeek;
            $dateEndWeekNum = $timedimension->findImmediateNextWeekOfCurrentWeek($dateStartWeekNum)->ixYearWeek;

            $startDate = date('Y-m-d', strtotime($date));
            $endDate = date('Y-m-d', strtotime("$startDate +7 days"));

            $request->request->set('startDate', $startDate);
            $request->request->set('endDate', $endDate);
            $request->request->set('days', 6);

            $request->request->set('startWeek', $dateStartWeekNum);
            $request->request->set('endWeek', $dateEndWeekNum);

            $request->request->set('startWeekDate', $startDate);
            $request->request->set('endWeekDate', $endDate);

            $weekYear = substr((string)$dateStartWeekNum, 0, 4);
            $weekNum = substr((string)$dateStartWeekNum, 4, 2);

            // Set week number in format "WW/YYYY"
            $request->request->set('weekNumber', "$weekNum/$weekYear");
        } else {
            $startDate = new Carbon();
            $weekNumber = $request->get('weekNumber', $startDate->format('W/Y'));
            [$week, $year] = explode('/', $weekNumber);

            $startDate->setISODate((int)$request->get('year', (int)$year), (int)$request->get('week', (int)$week));

            // Set default BBC start day (Saturday) - assuming Allocation::BBC_CARBON_DAYS[0] is integer day of week
            $startDate->startOfWeek(Allocation::BBC_CARBON_DAYS[0]);

            $endDate = (clone $startDate)->addWeeks((int)$request->get('weeks', 1));


            $request->request->set('startDate', $timedimension->getWeekStartDate($weekNumber)->dDateTime ?? '');
            $request->request->set('endDate', date('Y-m-d', strtotime('+1 day', strtotime((string) ($timedimension->getWeekEndDate($weekNumber)?->dDateTime ?? '')))));

            $request->request->set('startWeekDate', $timedimension->getWeekStartDate($weekNumber)->dDateTime ?? '');
            $request->request->set('endWeekDate', date('Y-m-d', strtotime('+1 day', strtotime((string) ($timedimension->getWeekEndDate($weekNumber)?->dDateTime ?? '')))));

            $startWeek = str_pad((string)$startDate->week, 2, '0', STR_PAD_LEFT);
            $endWeek = str_pad((string)$endDate->week, 2, '0', STR_PAD_LEFT);

            $startD = new DateTime($request->get('startDate'));
            $endD = new DateTime($request->get('endDate'));
            $intervalD = $startD->diff($endD);
            $days = ($intervalD->days) - 1;

            $request->request->set('days', $days);

            $startWeekIx = $timedimension->findByDateWithoutCarbon(date('Y-m-d', strtotime((string) ($timedimension->getWeekStartDate($weekNumber)?->dDateTime ?? ''))))?->ixYearWeek ?? '';
            $endWeekIx = $timedimension->findImmediateNextWeekOfCurrentWeek($startWeekIx)->ixYearWeek ?? '';

            $request->request->set('startWeek', $startWeekIx);
            $request->request->set('endWeek', $endWeekIx);
        }

        self::$request = $request;
        return $request;
    }
}

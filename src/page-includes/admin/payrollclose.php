<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
if (!isset($_REQUEST['year'])) {
  $intYear = date("Y", strtotime("-3 months"));
}
else {
  $intYear = $_REQUEST['year'];
}

$intLastYear = $intYear - 1;
$intNextYear = $intYear + 1;

$strQuery = "SELECT        T1.PayRollMonth, T1.[Account UpTo], T1.[PayRoll Date], T1.[PayRoll Process Date], T1.[BankCredit Date], T1.Status, T1.ixMonth, T1.ixYear,
                           SUM(CASE T2.fHolidayFlag WHEN 1 THEN 1 ELSE 0 END) AS PubHols
             FROM          AccountStatus AS T1
             INNER JOIN    TimeDimension AS T2 ON T1.ixMonth = T2.ixMonthInYear
             WHERE         (T1.PayRollMonth BETWEEN ".$intYear."04 AND ".$intNextYear."03)
             GROUP BY      T1.PayRollMonth, T1.[Account UpTo], T1.[PayRoll Date], T1.[PayRoll Process Date], T1.[BankCredit Date], T1.Status, T1.ixMonth, T1.ixYear
             ORDER BY      T1.PayRollMonth";

$db = OpenDatabase();
$rsDates = sqlsrv_query($db, $strQuery);

  echo '<table class="tablesmallnoborder"  width="100%">';
  echo '<tr>';
  echo '<td class="lightcell medtextbold handcursor" onclick=\'javascript:ShowPayrollClose('.$intLastYear.')\'>';
  echo '<< '.$intLastYear;
  echo '</td>';
  echo '<td align="center" class="lightcell medtextbold">';
  echo '<font size="3">';
  echo '<br>Monthly Payroll Timetable for '.$intYear.'/'.$intNextYear.'<br><br>';
  echo '</font>';
  echo '</td>';
  echo '<td align="right" class="lightcell medtextbold handcursor" onclick=\'javascript:ShowPayrollClose('.$intNextYear.')\'>';
  echo $intNextYear.' >>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '<br>';
  echo '<table class="tablesmalltidy" id="usersinfotable" width="100%">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo '<br>[*] Indicates Month with Bank Holiday<br><br>';
  echo '</th>';
  echo '<th>';
  echo 'Allocate/TAPS Closes at 12:00 Midday';
  echo '</th>';
  echo '<th>';
  echo 'Close up to the End of Week';
  echo '</th>';
  echo '<th>';
  echo 'Payroll Processing Date';
  echo '</th>';
  echo '<th>';
  echo 'BBC Bank Accounts Credited ';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';

  while ($row = sqlsrv_fetch_array($rsDates)) {
  echo '<tr>';
  echo '<td>';
  $payrollmonthyear = $row['PayRollMonth'];
  $payrollyear = substr($payrollmonthyear, 0, 4);
  $payrollmonth = substr($payrollmonthyear, 4, 2);
  $dateObj   = DateTime::createFromFormat('!m', $payrollmonth);
  $monthName = $dateObj->format('F');

  echo $monthName.' '.$payrollyear;
  if ($row['PubHols'] != 0) {
    echo '&nbsp;*';

  }
  echo '</td>';

  echo '<td>';
  echo spindate($row['PayRoll Date']);
  echo '</td>';

  echo '<td>';
  echo SpinWeekOrMonth($row['Account UpTo']);
  echo '</td>';

  echo '<td>';
  echo spindate($row['PayRoll Process Date']);
  echo '</td>';

  echo '<td>';
  echo spindate($row['BankCredit Date']);
  echo '</td>';

  }
<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
 
$intDepID = $_REQUEST['DepID']; 
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrAdminDepts = GetAdminDepts($strUser, 1);

if (isset($_REQUEST['submit'])) {
  $history = '';
  $strDepName = $_REQUEST['DepName'];
  $streMail = $_REQUEST['eMail'];
  $intMaskAfter = $_REQUEST['MaskAfter'];
  $intConfirmedDays = $_REQUEST['ConfirmedDays'];
  $intDailyEditPeriod = $_REQUEST['DailyEditPeriod'];
  $strEditStart = $_REQUEST['DailyEditStart'];
  $strEditEnd = $_REQUEST['DailyEditEnd'];
  $intSignInDays = $_REQUEST['SignInDays'];
  $intLocksEnd = $_REQUEST['LocksEnd'];  
  $intHideDailyView = $_REQUEST['HideDailyView'];
  if (isset($_REQUEST['WeeksView'])) { 
    $intWeeksView = $_REQUEST['WeeksView']; 
  }
  else {
    $intWeeksView = 999;
  }
   
  $intMaskType = $_REQUEST['MaskType'];   
  $intFlMasking = $_REQUEST['FlMasking'];  

  if (isset($_REQUEST['AutoImport'])) {
    $intAutoImport = 1;  
  }
  else {
    $intAutoImport = 0;  
  } 
       
  
  if (isset($_REQUEST['AllowEDP'])) {
    $intAllowEDP = 1;  
  }
  else {
    $intAllowEDP = 0;  
  }  

  if (isset($_REQUEST['AllowInBuilding'])) {
    $intAllowInBuilding = 1;  
  }
  else {
    $intAllowInBuilding = 0;  
  } 

  if (isset($_REQUEST['ColourWeek'])) {
    $intColourWeek = 1;  
  }
  else {
    $intColourWeek = 0;  
  }
  if (isset($_REQUEST['LocksRollWeek'])) {
    $intLocksRollWeek = 1;  
  }
  else {
    $intLocksRollWeek = 0;  
  }
  if (isset($_REQUEST['HasHandovers'])) {
    $intHasHandovers = 1;  
  }
  else {
    $intHasHandovers = 0;  
  }
  if (isset($_REQUEST['HasXmasPoints'])) {
    $intHasXmasPoints = 1;  
  }
  else {
    $intHasXmasPoints = 0;  
  }  
  
  if (isset($_REQUEST['DailyAutoWeekend'])) {
    $intDailyAutoWeekend = 1;  
  }
  else {
    $intDailyAutoWeekend = 0;  
  } 

  if (isset($_REQUEST['HasDutiesView'])) {
    $intHasDutiesView = 1;  
  }
  else {
    $intHasDutiesView = 0;  
  }   
  $strLeaveDate = $_REQUEST['LeaveDate'];  
 
  if (isset($_REQUEST['HasGridChecks'])) {
    $intHasGridChecks = 1;  
  }
  else {
    $intHasGridChecks = 0;  
  }  
     
  if (isset($_REQUEST['AutoLockToday'])) {
    $intAutoLockToday = 1;  
  }
  else {
    $intAutoLockToday = 0;  
  }   
  
  if ($strDepName != $arrAdminDepts[$intDepID]['Description']) {
    $history.= 'The Department name was changed from \''.$arrAdminDepts[$intDepID]['Description'].'\' to \''.$strDepName.'\'<br>'; 
  }
  if ($streMail != $arrAdminDepts[$intDepID]['eMail']) {  
    $history.= 'The eMail address was changed from \''.$arrAdminDepts[$intDepID]['eMail'].'\' to \''.$streMail.'\'<br>';   
  }
  
  
  if ($intMaskAfter != $arrAdminDepts[$intDepID]['MaskAfter']) {
    $history.= 'The number of days after Masking is applied was changed from \''.$arrAdminDepts[$intDepID]['MaskAfter'].'\' to \''.$intMaskAfter.'\'<br>'; 
  }  
  
  if ($intFlMasking != $arrAdminDepts[$intDepID]['FLMaskAfter']) {
    $history.= 'The number of days Freelancers can see was changed from \''.$arrAdminDepts[$intDepID]['FLMaskAfter'].'\' to \''.$intFlMasking.'\'<br>'; 
  }  
    
  $arrMaskTypes[0] = 'Show First Letter'; 
  $arrMaskTypes[1] = 'Hide all Allocations';
  
  if ($intMaskType != $arrAdminDepts[$intDepID]['MaskType']) {
    $history.= 'The Mask Type was changed from \''.$arrMaskTypes[$arrAdminDepts[$intDepID]['MaskType']].'\' to \''.$arrMaskTypes[$intMaskType].'\'<br>'; 
  }    
  
  
  if ($intConfirmedDays != $arrAdminDepts[$intDepID]['ConfirmedDays']) {
    $history.= 'The number of Fixed days was changed from \''.$arrAdminDepts[$intDepID]['ConfirmedDays'].'\' to \''.$intConfirmedDays.'\'<br>'; 
  }     
  if ($intLocksEnd !=  $arrAdminDepts[$intDepID]['LocksEnd']) {
    $history.= 'The number of Days Locks end was changed from \''.$arrAdminDepts[$intDepID]['LocksEnd'].'\' to \''.$intLocksEnd.'\'<br>'; 
  }    
  if ($intHideDailyView !=  $arrAdminDepts[$intDepID]['HideDailyView']) {
    $history.= 'The number of Locks per Week was changed from \''.$arrAdminDepts[$intDepID]['HideDailyView'].'\' to \''.$intHideDailyView.'\'<br>'; 
  }     
  if ($intDailyEditPeriod != $arrAdminDepts[$intDepID]['DailyEditPeriod']) {
    $history.= 'The number of days allowed for editing was changed from \''.$arrAdminDepts[$intDepID]['DailyEditPeriod'].'\' to \''.$intDailyEditPeriod.'\'<br>'; 
  }   
  if ($strEditStart != $arrAdminDepts[$intDepID]['DailyEditStart']) {
    $history.= 'The time when days become editable was changed from \''.$arrAdminDepts[$intDepID]['DailyEditStart'].'\' to \''.$strEditStart.'\'<br>'; 
  } 
  if ($strEditEnd != $arrAdminDepts[$intDepID]['DailyEditEnd']) {
    $history.= 'The time when days stop become editable was changed from \''.$arrAdminDepts[$intDepID]['DailyEditEnd'].'\' to \''.$strEditEnd.'\'<br>'; 
  }   
  if ($intSignInDays != $arrAdminDepts[$intDepID]['SignInDays']) {
    $history.= 'The number of days signing in is allowed was changed from \''.$arrAdminDepts[$intDepID]['SignInDays'].'\' to \''.$intSignInDays.'\'<br>'; 
  } 
  if ($intAllowInBuilding != $arrAdminDepts[$intDepID]['AllowInBuilding']) {
    if ($intAllowInBuilding == 1) {
      $history.= 'The ability for staff to indicate they are in the building was set to \'Allow\'<br>';     
    }  
    else {
      $history.= 'The ability for staff to indicate they are in the building was set to \' Not Allow\'<br>';      
    }
  } 
  if ($intAllowEDP != $arrAdminDepts[$intDepID]['AllowApplyOvertime']) {
    if ($intAllowEDP == 1) {
      $history.= 'The ability of staff to apply for overtime was set to \'Allow\'<br>';     
    }  
    else {
      $history.= 'The ability of staff to apply for overtime was set to \' Not Allow\'<br>';      
    }
  }

  if ($intColourWeek != $arrAdminDepts[$intDepID]['ColourWeek']) {
    if ($intColourWeek == 1) {
      $history.= 'The Colouring of a week was set to \'Colour\'<br>';     
    }  
    else {
      $history.= 'The Colouring of a week was set to \'Default\'<br>';      
    }
  }
  if ($intLocksRollWeek != $arrAdminDepts[$intDepID]['LocksRollWeek']) {
    if ($intLocksRollWeek == 1) {
      $history.= 'The Locking period end was changed to Rolling by Week<br>';     
    }  
    else {
      $history.= 'The Locking period end was changed to Rolling by Day<br>';      
    }
  }  
  if ($intHasHandovers != $arrAdminDepts[$intDepID]['HasHandovers']) {
    if ($intLocksRollWeek == 1) {
      $history.= 'The setting for Handovers was set to Yes<br>';     
    }  
    else {
      $history.= 'The setting for Handovers was set to No<br>';      
    }
  } 
 
  if ($intHasGridChecks != $arrAdminDepts[$intDepID]['HasGridChecks']) {
    if ($intHasGridChecks == 1) {
      $history.= 'The setting for Grid Checks was set to Show<br>';     
    }  
    else {
      $history.= 'The setting for Grid Checks was set to Hide<br>';      
    }
  }   
  if ($intAutoLockToday != $arrAdminDepts[$intDepID]['AutoLockToday']) {
    if ($intAutoLockToday == 1) {
      $history.= 'The setting for Automatically Locking current day was set to Yes<br>';     
    }  
    else {
      $history.= 'The setting for Automatically Locking current day was set to No<br>';      
    }
  }     
  if ($intHasXmasPoints != $arrAdminDepts[$intDepID]['HasXmasPoints']) {
    if ($intHasXmasPoints == 1) {
      $history.= 'The setting for Christmas Points was set to Yes<br>';     
    }  
    else {
      $history.= 'The setting for Christmas Points was set to No<br>';      
    }
  }  

  if ($intDailyAutoWeekend != $arrAdminDepts[$intDepID]['DailyAutoWeekend']) {
    if ($intDailyAutoWeekend == 1) {
      $history.= 'The setting for only editing Weekends was set to Yes<br>';     
    }  
    else {
      $history.= 'The setting for only editing Weekends was set to No<br>';      
    }
  } 



  if ($intHasDutiesView != $arrAdminDepts[$intDepID]['HasDutiesView']) {
    if ($intHasDutiesView == 0) {
      $history.= 'The setting for Has Production View was set to No<br>';     
    }  
    else {
      $history.= 'The setting for Has Production View was set to Yes<br>';      
    }
  } 

  if ($strLeaveDate != $arrAdminDepts[$intDepID]['LeaveHasMealDate']) {
      $history.= 'The setting for Leave Has Meal Breal from was set to '.$strLeaveDate.'<br>';      
  } 



  
  if ($intWeeksView != $arrAdminDepts[$intDepID]['WeeksView']) {
    $history.= 'The setting for The number of Weeks to view was changed from '.$arrAdminDepts[$intDepID]['WeeksView'].' to '.$intWeeksView.'<br>';     
  } 

  if ($intAutoImport != $arrAdminDepts[$intDepID]['AutoImport']) {
    if ($intAutoImport == 1) {
      $history.= 'The setting for Auto Importation of Allocations was changed to Yes<br>';     
    }  
    else {
      $history.= 'The setting for Auto Importation of Allocations was changed to No<br>';      
    }
  }    




  $strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
   	 /*
     @strAdminLogin		   VARCHAR(50),
	   @intDepID             INTEGER,
	   @strDepName           VARCHAR(50),  
     @intMaskAfter         INTEGER,
     @intMaskType          INTEGER,
     @intConfirmedDays     INTEGER,
     @intSignInDays        INTEGER,
     @intAllowEDP          INTEGER,
     @intAllowInBuilding   INTEGER,
     @intColourWeek        INTEGER,
	   @intDailyEditPeriod   INTEGER,
	   @strEditStart		     VARCHAR(10),
     @strEditEnd           VARCHAR(10),
	   @intLocksEnd          INTEGER,  
     @intHideDailyView      INTEGER,     
     @intLocksRollWeek     INTEGER,
     @intHasHandovers      INTEGER,
     @intHasXmasPoints     INTEGER
     HasGridChecks
     $streMail
     @intAutoImport     INTEGER
     @intWeeksView     INTEGER
     @intHasDutiesView  INTEGER
     @DailyAutoWeekend   INTEGER
     @intFlMasking       INTEGER
     @intAutoLockToday INTEGER
     @History              NVARCHAR(Max)
     
     
     
     */
    $db = OpenDatabase(); 
    $intResult = 1;
    $tsql_callSP = "{call usp_RED_UpdateDepartment( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}"; 
    $params = array(   
                   array($strLogin, SQLSRV_PARAM_IN),
                   array($intDepID, SQLSRV_PARAM_IN),
                   array($strDepName, SQLSRV_PARAM_IN),                                      
                   array($intMaskAfter, SQLSRV_PARAM_IN),
                   array($intMaskType, SQLSRV_PARAM_IN),
                   array($intConfirmedDays, SQLSRV_PARAM_IN),                   
                   array($intSignInDays, SQLSRV_PARAM_IN),                   
                   array($intAllowEDP, SQLSRV_PARAM_IN),
                   array($intAllowInBuilding, SQLSRV_PARAM_IN),                   
                   array($intColourWeek, SQLSRV_PARAM_IN),                   
                   array($intDailyEditPeriod, SQLSRV_PARAM_IN),                                      
                   array($strEditStart, SQLSRV_PARAM_IN),
                   array($strEditEnd, SQLSRV_PARAM_IN),
                   array($intLocksEnd, SQLSRV_PARAM_IN),
                   array($intHideDailyView, SQLSRV_PARAM_IN),
                   array($intLocksRollWeek, SQLSRV_PARAM_IN),
                   array($intHasHandovers, SQLSRV_PARAM_IN),
                   array($intHasXmasPoints, SQLSRV_PARAM_IN),
                   array($intHasGridChecks, SQLSRV_PARAM_IN),
                   array($streMail, SQLSRV_PARAM_IN),
                   array($intAutoImport, SQLSRV_PARAM_IN),
                   array($intWeeksView, SQLSRV_PARAM_IN), 
                   array($intHasDutiesView, SQLSRV_PARAM_IN),
                   array($intDailyAutoWeekend, SQLSRV_PARAM_IN),
                   array($intFlMasking, SQLSRV_PARAM_IN),
                   array($intAutoLockToday, SQLSRV_PARAM_IN),
                   array($strLeaveDate, SQLSRV_PARAM_IN),                   
                   array($history, SQLSRV_PARAM_IN),                                                         
                   );  
    $stmt = sqlsrv_query( $db, $tsql_callSP, $params);  
    if( $stmt === false ) {  
       echo "Error in executing statement 3.\n";  
       die( print_r( sqlsrv_errors(), true));  
    }
    else {
      $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
      echo $row['ReturnValue'];  
    }




  
}
else {
// ###################################################################### Get the departments this user can administer.....

  echo '<form id="deptedit">';
  echo '<table class="tablesmalltidy" width="800px">';
  echo '<tr>';
  echo '<th colspan="4">';
  echo '<br>Edit Department Information for '.$arrAdminDepts[$intDepID]['Description'].'.<br><br>';
  echo '</th>';
  echo '</tr>';
  echo '</table>';  
  echo '<div id="errorBox" class="lightcell">';   
  echo '</div>';
  
  echo '<table class="tablesmalltidy" width="800px">';  
  echo '<tr height="30px">';
  echo '<th>';
  echo 'Department Name';
  echo '</th>';
  echo '<td colspan="4">';
  echo '<input name="DepName" id="DepName" size="40" value="'.$arrAdminDepts[$intDepID]['Description'].'" type="text">';    
  echo '</td>';
  echo '</tr>';

  echo '<tr height="30px">';
  echo '<th>';
  echo 'eMail<br>Use semi-colon to separate multiple entries.';
  echo '</th>';
  echo '<td colspan="4">';
  echo '<input name="eMail" id="eMail" size="60" value="'.$arrAdminDepts[$intDepID]['eMail'].'" type="text">';    
  echo '</td>';
  echo '</tr>';
    
  echo '<tr height="30px">';  
  echo '<th>';
  echo 'Has Handovers<br>';
  echo '</th>';
  
  echo '<th>';
  echo 'Has Grid Checks<br>';
  echo '</th>';
        
  echo '<th>';                                       
  echo 'Has Christmas Points<br>';
  echo '</th>';
  
  echo '<th colspan="2">';                                       
  echo 'Has Production View<br>';
  echo '</th>';  
    
  echo '</tr>';  
  
  echo '<tr>';
  echo '<td valign="top">';
  echo '<input name="HasHandovers" id="HasHandovers" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['HasHandovers'] != 0) {
      echo ' checked';
  }
    echo '>';
  echo '</td>'; 

  echo '<td valign="top">';
  echo '<input name="HasGridChecks" id="HasGridChecks" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['HasGridChecks'] != 0) {
      echo ' checked';
  }
    echo '>';
  echo '</td>'; 
  
 
  
  echo '<td valign="top">';
  echo '<input name="HasXmasPoints" id="HasXmasPoints" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['HasXmasPoints'] != 0) {
      echo ' checked';
  }
    echo '>';
  echo '</td>'; 
 
  echo '<td valign="top" colspan="2">';
  echo '<input name="HasDutiesView" id="HasDutiesView" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['HasDutiesView'] != 0) {
      echo ' checked';
  }
  echo '>';
  echo '</td>';  
  echo '</tr>';  


  echo '<tr height="30px">'; 
  echo '<th colspan="5">';                                       
  echo 'Leave Has Meal Breaks until.<br>';
  echo '</th>';

  echo '</tr>';
  echo '<tr>';    
  
  /*
  echo '<td valign="top"> colspan="5"';
  echo '<input name="LeaveHasMeal" id="LeaveHasMeal" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['LeaveHasMealDate'] != 0) {
      echo ' checked';
  }
  echo '>';
  echo '</td>';
  */
  echo '<td align="right"><input type="hidden" name="LeaveDate" id="datepicker-start" value="'.$arrAdminDepts[$intDepID]['LeaveHasMealDate'].'" required/></td>';
  echo '<td colspan="4"><input type="text" id="Leavealternate" size="30" value="'.date("l, j F, Y", strtotime($arrAdminDepts[$intDepID]['LeaveHasMealDate'])).'" readonly="true"></td>';   
  echo '</tr>';   
  
    
  echo '<tr height="30px">';
  echo '<th width="160px">';
  echo 'Number of Days allowed for editing<br>';
  echo '</th>';

     
  echo '<th width="160px">';
  echo 'Editing Starts<br>';
  echo '</th>';

  echo '<th width="160px">';
  echo 'Editing Ends<br>';
  echo '</th>';
  
  echo '<th width="160px">';
  echo 'Weekends Only<br>';
  echo '</th>';
  
  echo '<th width="160px">';
  echo 'Lock Current Day on Timer';
  echo '</th>';
    
  echo '</tr>';

  echo '<tr>';
    
  echo '<td valign="top">';
  echo '<input name="DailyEditPeriod" id="DailyEditPeriod" size="10" value="'.$arrAdminDepts[$intDepID]['DailyEditPeriod'].'" type="text">';
  echo '</td>';
  
  echo '<td valign="top">';
  echo '<input class="time" name="DailyEditStart" id="DailyEditStart" size="10" value="'.$arrAdminDepts[$intDepID]['DailyEditStart'].'" type="text">';
  echo '</td>';

  echo '<td valign="top">';
  echo '<input class="time" name="DailyEditEnd" id="DailyEditEnd" size="10" value="'.$arrAdminDepts[$intDepID]['DailyEditEnd'].'" type="text">';
  echo '</td>';
  
  echo '<td valign="top">';  
  echo '<input name="DailyAutoWeekend" id="DailyAutoWeekend" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['DailyAutoWeekend'] != 0) {
      echo ' checked';
  }
  echo '>';  
  echo '</td>';

  echo '<td valign="top">';  
  echo '<input name="AutoLockToday" id="AutoLockToday" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['AutoLockToday'] != 0) {
      echo ' checked';
  }
  echo '>';  
  echo '</td>';
    
  echo '</tr>';

  echo '<tr height="30px">';
  echo '<th colspan="5">';
  echo 'Number of Days the Daily View is visible (Except Schedulers)<br>';
  echo '</th>';
  echo '</tr>';
  
  echo '<tr>';
  echo '<td valign="top" colspan="5">';
  echo '<input name="HideDailyView" id="HideDailyView" size="20" value="'.$arrAdminDepts[$intDepID]['HideDailyView'].'" type="text">';
  echo '</td>';
  echo '</tr>'; 
    
  echo '<tr height="30px">';
  echo '<th>';
  echo 'Number of Days for Signing in';
  echo '</th>';  
  
  echo '<th>';
  echo 'Allow Staff to indicate they are in the building';
  echo '</th>';
  
  echo '<th>';
  echo 'Allow People to apply for Overtime';
  echo '</th>';  

  echo '<th colspan="2">';
  echo 'Colour Cells on the Weekly/Monthly Views';
  echo '</th>';

  echo '</tr>';  
  
  echo '<tr>';
  echo '<td>';
  echo '<input name="SignInDays" id="SignInDays" size="20" value="'.$arrAdminDepts[$intDepID]['SignInDays'].'" type="text">';
  echo '</td>';  
  
  echo '<td valign="top">';
  echo '<input name="AllowInBuilding" id="AllowInBuilding" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['AllowInBuilding'] != 0) {
      echo ' checked';
  }
    echo '>';
  echo '</td>';
  
  echo '<td valign="top">';
  echo '<input name="AllowEDP" id="AllowEDP" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['AllowApplyOvertime'] != 0) {
      echo ' checked';
  }
    echo '>';
  echo '</td>';  

  echo '<td valign="top" colspan="2">';
  echo '<input name="ColourWeek" id="ColourWeek" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['ColourWeek'] != 0) {
      echo ' checked';
  }
    echo '>';
  echo '</td>';

  echo '</tr>';



  if ($intDepID < 0) {  
    echo '<tr height="30px">';    
    echo '<th>';
    echo 'Mask After<br>';
    echo '</th>';
    echo '<th>';
    echo 'Mask Type<br>';
    echo '</th>';    
    echo '<th>';
    echo 'Weeks on View<br>(Non Allocate Departments)';
    echo '</th>';
    echo '<th colspan="2">';
    echo 'Auto Import Allocations<br>(Non Allocate Departments)';
    echo '</th>';   
    echo '</tr>';
    
    echo '<tr>';     
    echo '<td valign="top">';
    echo '<input name="MaskAfter" id="MaskAfter" size="20" value="'.$arrAdminDepts[$intDepID]['MaskAfter'].'" type="text">';
    echo '</td>';
    
    echo '<td valign="top">';
    echo '<select size="1" name="MaskType">';
    echo '<option value="0"';
    if ($arrAdminDepts[$intDepID]['MaskType'] == 0) {
      echo 'selected';
    }
    echo '>Show First Letter</option>';
    echo '<option value="1"';
    if ($arrAdminDepts[$intDepID]['MaskType'] == 1) {
      echo 'selected';
    }
    echo '>Hide All Allocations</option>';    
    echo '</select>';
    echo '</td>';
    
    echo '<td valign="top">';
    echo '<input name="WeeksView" id="WeeksView" size="20" value="'.$arrAdminDepts[$intDepID]['WeeksView'].'" type="text">';
    echo '</td>';
    
    echo '<td colspan="2" valign="top">';
    echo '<input name="AutoImport" id="AutoImport" value="ON" type="checkbox"';
    if ($arrAdminDepts[$intDepID]['AutoImport'] != 0) {
        echo ' checked';
    }
    echo '>';
    echo '</td>'; 
    echo '</tr>';
  }
  else {
    echo '<tr height="30px">';    
    echo '<th>';
    echo 'Mask After<br>';
    echo '</th>';
    echo '<th colspan="4">';
    echo 'Mask Type<br>';
    echo '</th>';     
    echo '</tr>';

    echo '<tr>';     
    echo '<td valign="top">';
    echo '<input name="MaskAfter" id="MaskAfter" size="20" value="'.$arrAdminDepts[$intDepID]['MaskAfter'].'" type="text">';
    echo '</td>';
    echo '<td valign="top" colspan="4">';
    echo '<select size="1" name="MaskType">';
    echo '<option value="0"';
    if ($arrAdminDepts[$intDepID]['MaskType'] == 0) {
      echo 'selected';
    }
    echo '>Show First Letter</option>';
    echo '<option value="1"';
    if ($arrAdminDepts[$intDepID]['MaskType'] == 1) {
      echo 'selected';
    }
    echo '>Hide All Allocations</option>';    
    echo '</select>';
    echo '</td>';    
    
    echo '</tr>';
  }

  echo '<tr height="30px">'; 
  echo '<th colspan="5">';
  echo 'Freelance Masking (days)';
  echo '</th>';    
  echo '</tr>';

  echo '<tr>'; 
  echo '<td colspan="5">';
  echo '<input name="FlMasking" id="FlMasking" size="20" value="'.$arrAdminDepts[$intDepID]['FLMaskAfter'].'" type="text">';
  echo '</td>';    
  echo '</tr>';    
  
  echo '<tr>';
  echo '<th>';
  echo 'Locks Start<br>(Set to -1 to Disable Locks)';
  echo '</th>';    
  echo '<th>';
  echo 'Locks End (Days)';
  echo '</th>';

  echo '<th colspan="3">';
  echo 'Locks Roll Weeks';
  echo '</th>';
    
  //echo '<th>';
  //echo 'Locks Per Week';
  //echo '</th>';
  echo '</tr>';
  
  echo '<tr>';
  echo '<td valign="top">';
  echo '<input name="ConfirmedDays" id="ConfirmedDays" size="20" value="'.$arrAdminDepts[$intDepID]['ConfirmedDays'].'" type="text">';
  echo '</td>';
        
  echo '<td valign="top">';
  echo '<input name="LocksEnd" id="LocksEnd" size="20" value="'.$arrAdminDepts[$intDepID]['LocksEnd'].'" type="text">';
  echo '</td>';
  
  echo '<td colspan="3" valign="top">';
  echo '<input name="LocksRollWeek" id="LocksRollWeek" value="ON" type="checkbox"';
  if ($arrAdminDepts[$intDepID]['LocksRollWeek'] != 0) {
      echo ' checked';
  }
    echo '>';
  echo '</td>';  
  
  //echo '<td valign="top">';
  //echo '<input name="HideDailyView" id="HideDailyView" size="20" value="'.$arrAdminDepts[$intDepID]['HideDailyView'].'" type="text">';
  //echo '</td>';
  //echo '</tr>';
    
  echo '<tr>';
  echo '<td></td>';
  echo '<td colspan="3"><input value="Update" name="submit" type="submit">&nbsp;&nbsp;<input type="button" name="Cancel" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  
  echo '<input type="hidden" name="DepID" value="'.$intDepID.'">';
  echo '</form>';
  echo '</table>';

?>
<script language="JavaScript" type="text/javascript">
$('#deptedit').validate({
  errorLabelContainer: "#errorBox",
  rules: { 
    DepName: {
      required: true,
      maxlength: 30
    },  
    DailyEditPeriod: {
      required: true,
      number: true
    },  
    SignInDays: {
      required: true,
      number: true
    },
    ConfirmedDays: {
      required: true,
      number: true
    },    
    MaskAfter: {
      required: true,
      number: true
    },    
     DailyEditStart: {
      required: true
    },
     DailyEditEnd: {
      required: true
    },
    LocksEnd: {
      required: true,
      number: true
    },    
    HideDailyView: {
      required: true,
      number: true
    },    
    FlMasking: {
      required: true,
      number: true
    },    
  },
  messages: {
    DepName: "Please Enter a Name for this Department<br>",
    DailyEditPeriod: "Please Enter a number for the Daily Edit Period (-1 to disallow editing)<br>",
    SignInDays: "Please Enter a number for Signing in (-1 to disallow Signing In)<br>",
    ConfirmedDays: "Please Enter a number for how many days are displayed as fixed<br>",
    MaskAfter: "Please Enter a number for how many days are dipaleyed before masking is applied<br>",      
    DailyEditStart: "Please Enter a time Daily Editing starts<br>", 
    DailyEditEnd: "Please Enter a time Daily Editing ends<br>",
    LocksEnd: "Please Enter the number of days that locks end<br>",
    LocksEnd: "Please Enter the number of Locks per week that are allowed<br>"     
  },
  submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/admin/deptsedit.php', data:$('#deptedit').serialize(), success: function(data) {
        $.facebox.close();        
        ShowDepartments();           
      }});
  } 
});


$(function() {
  $('#DailyEditStart').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

$(function() {
  $('#DailyEditEnd').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

  $(function() {
    $( "#datepicker-start" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#Leavealternate",
      altFormat: "DD, d MM, yy"      
    });
  }); 
</script>    


<?php
}


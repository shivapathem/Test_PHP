<?php
session_start();
include_once '../../function-includes/init.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$strUserName = $_SESSION['user']['FullName'];
$db = OpenDatabase();
                        
$strQuery = "SELECT            Departments.id as DepartmentID, Departments.FullName as DepartmentName, Staff_Web_Config_Departments_Link.ManagerLogin, Staff_Web_Config_Departments_Link.MentorLogin, 
                               Staff_Web_Config_Departments_Link.AppraiserLogin, Staff_Manager.Login AS ManagerLogin, 
                               Staff_Manager.Forename + N' ' + Staff_Manager.Surname AS ManagerName, Staff_Appraiser.Login AS AppraiserLogin, 
                               Staff_Appraiser.Forename + N' ' + Staff_Appraiser.Surname AS AppraiserName, Staff_Mentor.Login AS MentorLogin, 
                               Staff_Mentor.Forename + N' ' + Staff_Mentor.Surname AS MentorName
             FROM              Staff 
             INNER JOIN        Departments ON Staff.DepartmentID = Departments.ID 
             INNER JOIN        Staff_Web_Config_Departments_Link ON Staff.DepartmentID = Staff_Web_Config_Departments_Link.DepartmentID AND Staff.Login = Staff_Web_Config_Departments_Link.Login 
             LEFT OUTER JOIN   Staff AS Staff_Appraiser ON Staff_Web_Config_Departments_Link.AppraiserLogin = Staff_Appraiser.Login AND Staff_Web_Config_Departments_Link.DepartmentID = Staff_Appraiser.DepartmentID 
             LEFT OUTER JOIN   Staff AS Staff_Manager ON Staff_Web_Config_Departments_Link.DepartmentID = Staff_Manager.DepartmentID AND Staff_Web_Config_Departments_Link.ManagerLogin = Staff_Manager.Login 
             LEFT OUTER JOIN   Staff AS Staff_Mentor ON Staff_Web_Config_Departments_Link.MentorLogin = Staff_Mentor.Login AND Staff_Web_Config_Departments_Link.DepartmentID = Staff_Mentor.DepartmentID
             WHERE            (Staff.Login = N'$strUser')";                         
                         
                         
                         
  //echo $strQuery;
  $rsSupport = sqlsrv_query($db, $strQuery);

  while($row = sqlsrv_fetch_array($rsSupport)) {
    $arrSupport[$row['DepartmentID']]['DepartmentName'] = $row['DepartmentName'];
    $arrSupport[$row['DepartmentID']]['Manager'] = $row['ManagerName'];
    $arrSupport[$row['DepartmentID']]['ManagerLogin'] = strtolower($row['ManagerLogin']);
    $arrSupport[$row['DepartmentID']]['Appraiser'] = $row['AppraiserName'];    
    $arrSupport[$row['DepartmentID']]['AppraiserLogin'] = strtolower($row['AppraiserLogin']);   
    $arrSupport[$row['DepartmentID']]['Mentor'] = $row['MentorName'];     
    $arrSupport[$row['DepartmentID']]['MentorLogin'] = strtolower($row['MentorLogin']);  
  }
  echo '<table class="tablesmall" width="700px">';
  echo '<tr>';
  echo '<th colspan="2" align="center">';
  echo '<br>Support Contacts for '.$strUserName.'<br><br>';
  echo '</th>';
  echo '</tr>';
  if (isset($arrSupport)) {
    foreach ($arrSupport as $intDepartmentID => $arrDepartment) {
      echo '<tr>';
      echo '<th colspan="2" align="center">';
      echo '<br>'.$arrDepartment['DepartmentName'].'<br><br>';
      echo '</th>';
      echo '</tr>';
    
      if ($arrDepartment['Manager'] != '') {
        echo '<tr>';
        echo '<td class="medlightcell" valign="center" align="center">';
        echo 'Your Manager in '.$arrDepartment['DepartmentName'].' is '.$arrDepartment['Manager'];
        echo '</td>';
        echo '<td class="medlightcell" align="center">';
        $mgrLogin = $arrDepartment['ManagerLogin'];
        $pics = glob(getenv('STAFF_IMAGE_UPLOAD_DIR').$mgrLogin.'.*');
        if (count($pics) > 0) {
          $picname = explode(DIRECTORY_SEPARATOR, $pics[0]);
          $picname = $picname[count($picname) -1];
          echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').$picname.'?t='.time().'" width="100px">';
        }
        else {
          echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').'no.gif" width="100px">';
        }
        echo '</td>';      
        echo '</tr>';
      }  
  
      if ($arrDepartment['Appraiser'] != '') {
        echo '<tr>';
        echo '<td class="medlightcell" valign="center" align="center">';
        echo 'Your Appraiser in '.$arrDepartment['DepartmentName'].' is '.$arrDepartment['Appraiser'];
        echo '</td>';
        echo '<td class="medlightcell" align="center">';
        $mgrLogin = $arrDepartment['AppraiserLogin'];
        $pics = glob(getenv('STAFF_IMAGE_UPLOAD_DIR').$mgrLogin.'.*');
        if (count($pics) > 0) {
          $picname = explode(DIRECTORY_SEPARATOR, $pics[0]);
          $picname = $picname[count($picname) -1];
          echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').$picname.'?t='.time().'" width="100px">';
        }
        else {
          echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').'no.gif" width="100px">';
        }
        echo '</td>';      
        echo '</tr>';
      } 
    
      if ($arrDepartment['Mentor'] != '') {
        echo '<tr>';
        echo '<td class="medlightcell" valign="center" align="center">';
        echo 'Your Mentor in '.$arrDepartment['DepartmentName'].' is '.$arrDepartment['Mentor'];
        echo '</td>';
        echo '<td class="medlightcell" align="center">';
        $mgrLogin = $arrDepartment['MentorLogin'];
        $pics = glob(getenv('STAFF_IMAGE_UPLOAD_DIR').$mgrLogin.'.*');
        if (count($pics) > 0) {
          $picname = explode(DIRECTORY_SEPARATOR, $pics[0]);
          $picname = $picname[count($picname) -1];
          echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').$picname.'?t='.time().'" width="100px">';
        }
        else {
          echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').'no.gif" width="100px">';
        }
        echo '</td>';      
        echo '</tr>';
      } 
    }
  }
  else {
  
  echo '<tr>';
  echo '<th colspan="2" align="center">';
  echo '<br>You do not have a Manager, Appraisor or Mentor defined!<br><br>';
  echo '</th>';
  echo '</tr>';  
  
  
  
  }
  echo '</table>';  
  
  
  
  
  
  
  
  
  
  
  
  
  
  die;
  
  
  
  
  // Manager
  if ($row['ManagerStaffID'] != 0) {
    echo '<tr>';
    echo '<th width="350px"><blockquote>';
    echo 'My Line Manager is ';
    echo $row['Manager'];
    echo '</blockquote></th>';
    echo '<td class="medlightcell" align="center">';
    $mgrLogin = strtolower($row['ManagerLogin']);
    $pics = glob(getenv('STAFF_IMAGE_UPLOAD_DIR').$mgrLogin.'.*');
    // print_r($pics);
    //$pics = glob(sql_regcase('/var/www/allocations/images/staffpics/'.$row['ManagerLogin'].'.*'));
    if (count($pics) > 0) {
      $picname = explode(DIRECTORY_SEPARATOR, $pics[0]);
      $picname = $picname[count($picname) -1];
      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').$picname.'?t='.time().'" width="100px">';
    }
    else {
      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').'no.gif" width="100px">';
    }
    echo '</td>';
    echo '</tr>';
  }
  // Apraiser
  if ($row['AppraiserStaffID'] != 0) {
    echo '<tr>';
    echo '<th width="350px"><blockquote>';
    echo 'My Appraiser is ';
    echo $row['Appraiser'];
    echo '</blockquote></th>';
    echo '<td class="medlightcell" align="center">';
    $appLogin = strtolower($row['AppraiserLogin']);
    $pics = glob(getenv('STAFF_IMAGE_UPLOAD_DIR').$appLogin.'.*');
    //$pics = glob(sql_regcase('/var/www/allocations/images/staffpics/'.$row['AppraiserLogin'].'.*'));
    if (count($pics) > 0) {
      $picname = explode(DIRECTORY_SEPARATOR, $pics[0]);
      $picname = $picname[count($picname) -1];

      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').$picname.'?t='.time().'" width="100px">';
  }
  else {

      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').'no.gif" width="100px">';
  }
    echo '</td>';
    echo '</tr>';
  }
  // Mentor
  if ($row['MentorStaffID'] != 0) {
  echo '<tr>';
  echo '<th width="350px"><blockquote>';
  echo 'My Mentor is ';
  echo $row['Mentor'];
  echo '</blockquote></th>';
  echo '<td class="medlightcell" align="center">';
  $mtrLogin = strtolower($row['MentorLogin']);
  $pics = glob(getenv('STAFF_IMAGE_UPLOAD_DIR').$mtrLogin.'.*');

  //$pics = glob(sql_regcase('/var/www/allocations/images/staffpics/'.$row['MentorLogin'].'.*'));
 if (count($pics) > 0) {
      $picname = explode(DIRECTORY_SEPARATOR, $pics[0]);
      $picname = $picname[count($picname) -1];

      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').$picname.'?t='.time().'" width="100px">';
  }
  else {

      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').'no.gif" width="100px">';
  }
  echo '</td>';
  echo '</tr>';
  }
 echo '</table>';

//include_once 'usefullinks.php';
?>
<?php

function GetStaffOnAttachment() {

$db = OpenDatabase();
$date = date("Y-m-15");

for ($i = 0; $i <12; $i++) {

  $query = "SELECT COUNT(staff_status.id) AS CountAttachees, staff_status.IsOnAttach, Staff.DefaultDepartmentID
            FROM staff_status
            INNER JOIN Staff ON staff_status.staffnumber = Staff.PersonnelNumber
            AND staff_status.AllocateInstanceID = Staff.AllocateInstanceID
            WHERE (staff_status.attachstartdate <= CONVERT(DATETIME, '$date 00:00:00', 102))
            AND   (staff_status.attachenddate >= CONVERT(DATETIME, '$date 00:00:00', 102))
            AND Staff.AllocateInstanceID = " . getCurrentInstanceId() ."
            GROUP BY staff_status.IsOnAttach, Staff.DefaultDepartmentID
            HAVING        (staff_status.IsOnAttach <> 0)";


  //echo $query.'<br>';
  $rsAttachments = sqlsrv_query($db, $query);
  while ($row = sqlsrv_fetch_array($rsAttachments)) {
    $arrAttachments[$i][$row['DefaultDepartmentID']][$row['IsOnAttach']]= $row['CountAttachees'];
  }
  $date = date("Y-m-d", strtotime("+1 month", strtotime($date)));
}

  if (isset($arrAttachments)) {
    return ($arrAttachments);
  }
}


function ShowPhotos($intDepartment) {


// Holder for Internal/External
echo '<table border="0">';
echo '<tr>';
echo '<td valign="top">';

// Table for the people (Internal)
  $query = "SELECT        Staff.Forename + N' ' + Staff.Surname AS FullName, Staff.Login, Staff.Designation, Staff.Email1, staff_status.IsOnAttach,
                         staff_status.AttachNotes, Staff.DepartmentID, LTRIM(Staff_1.Forename + N' ' + Staff_1.Surname) AS Manager,
                         LTRIM(Staff_2.Forename + N' ' + Staff_2.Surname) AS Appraiser, LTRIM(Staff_3.Forename + N' ' + Staff_3.Surname) AS Mentor
            FROM            Staff
            INNER JOIN staff_status ON Staff.StaffNumber = staff_status.staffnumber
            AND Staff.AllocateInstanceID = staff_status.AllocateInstanceID
            LEFT OUTER JOIN
                         Staff AS Staff_3 ON staff_status.MentorStaffID = Staff_3.ID
            LEFT OUTER JOIN
                         Staff AS Staff_2 ON staff_status.AppraiserStaffID = Staff_2.ID
            LEFT OUTER JOIN
                         Staff AS Staff_1 ON staff_status.ManagerStaffID = Staff_1.ID
            WHERE        (Staff.showphoto = 1) AND (Staff.DefaultDepartmentID = $intDepartment) AND (staff_status.IsOnAttach = 1)
            AND Staff.AllocateInstanceID = " . getCurrentInstanceId() ."
            ORDER BY Staff.Surname, Staff.Forename";


//echo $query;
$staff = sqlsrv_query($db, $query);

echo '<table class="redtable">';
echo '<tr>';
echo '<th width="350px">';
echo 'Internal';
echo '</th>';
echo '</tr>';

while ($row = sqlsrv_fetch_array($staff)) {
  $login = $row['Login'];
  echo '<tr>';
  echo '<td>';

  echo '<table class= "tablesmallnoborder">';
  echo '<tr>';
  echo '<td class="DutyCellWorking" width="200px" bgcolor="#eeeeee">';
  echo '<font size="2"><b>'.$row['FullName'].'</b></font><br>';

  echo '<font size="1"><b>'.$row['Designation'].'</b></font><br><br>';
  echo '<i>Contact:</i><br>';
  echo '<a href="mailto:'.$row['Email1'].'">'.$row['FullName'].'</a>';
  //echo '<br>';

  if (!is_null($row['Appraiser']) && $row['Appraiser'] !='') {
    echo '<br>'.$row['Appraiser'].' (Appraiser)<br>';
  }
  echo '<br><b>'.$row['AttachNotes'].'</b><br>';



  echo '</td>';
  echo '<td class="DutyCellWorking" align="center" width="150px" bgcolor="#eeeeee">';




  $pics = glob(getenv('STAFF_IMAGE_UPLOAD_DIR').$login.'.*');
  if (count($pics) > 0) {
    $picname = substr($pics[0], strrpos($pics[0], '/') + 1);
    //echo '<img border="0" src="http://newsw1prodops1101.national.core.bbc.co.uk/allocations/images/staffpics/'.$picname.'" width="100px" height="134">';
    echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').$picname.'" width="100px" height="134">';
  }
  else {
    //echo '<img border="0" src="http://newsw1prodops1101.national.core.bbc.co.uk/allocations/images/staffpics/no.gif">';
      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').'no.gif">';
  }



  //$pics = glob(sql_regcase('/var/www/allocations/images/staffpics/'.$login.'.*'));


  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</td>';

  echo '</tr>';
}

echo '</table>';
// End the people


echo '</td>';
echo '<td valign="top">';

// Table for the people (External)
/*  REM:  No need for instance ID as departments will be unique to instances  */
  $query = "SELECT            Staff.Forename + N' ' + Staff.Surname AS FullName, Staff.Login, Staff.Designation, Staff.Email1, staff_status.IsOnAttach,
                              staff_status.AttachNotes, Staff.DepartmentID, LTRIM(Staff_1.Forename + N' ' + Staff_1.Surname) AS Manager,
                              LTRIM(Staff_2.Forename + N' ' + Staff_2.Surname) AS Appraiser, LTRIM(Staff_3.Forename + N' ' + Staff_3.Surname) AS Mentor
            FROM              Staff
            INNER JOIN        staff_status ON Staff.StaffNumber = staff_status.staffnumber
            AND Staff.AllocateInstanceID = staff_status.AllocateInstanceID
            LEFT OUTER JOIN   Staff AS Staff_3 ON staff_status.MentorStaffID = Staff_3.ID
            LEFT OUTER JOIN   Staff AS Staff_2 ON staff_status.AppraiserStaffID = Staff_2.ID
            LEFT OUTER JOIN   Staff AS Staff_1 ON staff_status.ManagerStaffID = Staff_1.ID
            WHERE             (Staff.showphoto = 1)
            AND               (Staff.DefaultDepartmentID = $intDepartment)
            AND               (staff_status.IsOnAttach = 2)
            AND Staff.AllocateInstanceID = " . getCurrentInstanceId() ."
            ORDER BY Staff.Surname, Staff.Forename";

$staff = sqlsrv_query($db, $query);
echo '<table class="redtable">';
echo '<tr>';
echo '<th width="350px">';
echo 'External';
echo '</th>';
echo '</tr>';

while ($row = sqlsrv_fetch_array($staff)) {
  $login = $row['Login'];
  echo '<tr>';
  echo '<td>';

  echo '<table class= "tablesmallnoborder">';
  echo '<tr>';
  echo '<td class="DutyCellWorking" width="200px" bgcolor="#eeeeee">';
  echo '<font size="2"><b>'.$row['FullName'].'</b></font><br>';

  echo '<font size="1"><b>'.$row['Designation'].'</b></font><br><br>';
  echo '<i>Contact:</i><br>';
  echo '<a href="mailto:'.$row['Email1'].'">'.$row['FullName'].'</a>';
  //echo '<br>';

  if (!is_null($row['Appraiser']) && $row['Appraiser'] !='') {
    echo '<br>'.$row['Appraiser'].' (Appraiser)<br>';
  }
  echo '<br><b>'.$row['AttachNotes'].'</b><br>';



  echo '</td>';
  echo '<td class="DutyCellWorking" align="center" width="150px" bgcolor="#eeeeee">';


  $pics = glob(getenv('STAFF_IMAGE_UPLOAD_DIR').$login.'.*');
  if (count($pics) > 0) {
    $picname = substr($pics[0], strrpos($pics[0], '/') + 1);
    //echo '<img border="0" src="http://newsw1prodops1101.national.core.bbc.co.uk/allocations/images/staffpics/'.$picname.'" width="100px" height="134">';
    echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').$picname.'" width="100px" height="134">';
  }
  else {
    //echo '<img border="0" src="http://newsw1prodops1101.national.core.bbc.co.uk/allocations/images/staffpics/no.gif">';
      echo '<img border="0" src="'.getenv('STAFF_IMAGE_URL').'no.gif">';
  }



 // $pics = glob(sql_regcase('/var/www/allocations/images/staffpics/'.$login.'.*'));


  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</td>';

  echo '</tr>';
}


echo '</table>';
// End the people

echo '</td>';
echo '</tr>';
echo '</table>';









}



?>
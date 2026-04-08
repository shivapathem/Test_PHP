<?php
include_once '../function-includes/init.php';
$db = OpenDatabase();


$query = "SELECT Staff.Forename + N' ' + Staff.Surname AS FullName, Staff.Login, Staff.Designation, Staff.Email1, staff_status.IsOnAttach,
          staff_status.AttachNotes, LTRIM(Staff_1.Forename + N'  ' + Staff_1.Surname) AS Manager, LTRIM(Staff_2.Forename + ' ' + Staff_2.Surname) AS Appraiser,
          Staff_3.Forename + N' ' + Staff_3.Surname AS Mentor
          FROM Staff AS Staff_3
          RIGHT OUTER JOIN staff_status ON Staff_3.ID = staff_status.MentorStaffID
          AND Staff_3.AllocateInstanceID = staff_status.AllocateInstanceID
          LEFT OUTER JOIN Staff AS Staff_2 ON staff_status.AppraiserStaffID = Staff_2.ID
          LEFT OUTER JOIN Staff AS Staff_1 ON staff_status.ManagerStaffID = Staff_1.ID
          RIGHT OUTER JOIN Staff ON staff_status.staffnumber = Staff.StaffNumber
          AND staff_status.AllocateInstanceID = Staff.AllocateInstanceID
          WHERE  (Staff.showphoto = 1) AND (Staff.leavebases LIKE N'$base') AND (NOT (Staff.Login IS NULL)) AND (Staff.Login <> N'')
          OR   (Staff.showphoto = 1) AND (NOT (Staff.Login IS NULL)) AND (Staff.Login <> N'')";

          if ($base == 999) {
            $query.= " AND isonattachment = 1";
          }
          else {
            $query.= " AND (Staff.RadioBase = $base)";
          }
$query.= " ORDER BY Staff.Surname, Staff.Forename";
echo $query;
$staff = sqlsrv_query($db, $query);
echo '<table class="tablesmall">';
$i = 2;
while ($row = sqlsrv_fetch_array($staff)) {
  $login = $row['Login'];
  if (is_int($i / 2)) {
    echo '<tr>';
  }
  echo '<td>';

  echo '<table class= "tablesmallnoborder">';
  echo '<tr>';
  echo '<td class="DutyCellWorking" width="250px" bgcolor="#eeeeee">';
  echo '<font size="2"><b>'.$row['FullName'].'</b></font><br><br>';

  echo '<font size="1"><b>'.$row['Designation'].'</b></font><br>';
  echo '<i>Contact:</i><br>';
  echo '<a href="mailto:'.$row['Email1'].'">'.$row['FullName'].'</a>';
  echo '<br>';
  if (!is_null($row['Manager']) && $row['Manager'] !='') {
    echo '<br>'.$row['Manager'].' (Manager)';
  }
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

  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</td>';

  if (!is_int($i / 2)) {
    echo '</tr>';
  }

  $i++;
}
echo '</table>';
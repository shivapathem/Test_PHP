<?php
date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userGroupfunctions.php';

$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intID = $_REQUEST['id'];

//print_r($arrMyBases);
if ($intID == 0) {
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1000px"><br>You do not have any groups defined.
        <br>Click on the \'New\' icon at the top of the page
        <br>to define a new group.<br><br></div>';
}
else {
  // Get the people in this group 
  $arrInGroup = GetUserFavouritesInGroup ($intID);
 
  if (isset($arrInGroup['Team'])) {
    $intTeam = $arrInGroup['Team'];
  }
  else {
    $intTeam = 0;
  }
 
  $arrStaffAvailable = GetUserFavouritesNotInGroup($intTeam, $userId);  

  echo '<div id="myfavs">';
  echo '<table class="tablesmalltidy">';
  
  echo '<tr>';  
  echo '<th colspan="2">';  
  if ($intTeam == 0) {
    echo '<br>You do not have anyone in this group yet.<br>The Staff available are those in Scheduling Groups you have access to.<br>Once you add a person only staff in the Team for that person will be available.<br><br>';
  }
  else {
    echo '<br>Showing Staff in '.GetTeamNameByID($intTeam).'.<br><br>';
  }
  echo '</th>';
  echo '</tr>';
  
  echo '<tr>';  
  echo '<th width="400px">';  
  echo '<br>Available Staff<br><br>';
  echo '</th>';
  echo '<th width="400px">';  
  echo '<br>My Staff<br><br>'; 
  echo '</th>';  
  echo '</tr>';
  
  echo '<tr>';
  echo '<td valign="top">';
  echo '<div id="availpeople">';
  echo '<table id="TableStaffAvailable" class="tablesmall compact stripe" width="100%">';
  echo '<thead>';  
  echo '<tr>'; 
  echo '<th>'; 
  echo 'Name';    
  echo '</th>'; 
  echo '</tr>'; 
  echo '</thead>';       
  
  echo '<tbody>';    
  // Can I see them?
  if (!empty($arrStaffAvailable)) {
    foreach ($arrStaffAvailable as $strCurrStaffNumber => $strFullName) {
      if (!isset($arrInGroup['Users'][$strCurrStaffNumber])) {
        echo '<tr>';
        echo '<td class="handcursor" onclick="javascript:AddUser(\''.$strCurrStaffNumber.'\','.$intID.')";>';
        echo $strFullName;
        echo '</td>';
        echo '</tr>';
      }
    }
  }
  echo '</tbody>';  
  echo '</table>';
  echo '</div>';
  echo '</td>';

  echo '<td valign="top">';
  echo '<div id="alreadypeople">';  
  echo '<table width="100%">';
  echo '<table id="TableStaffInGroup" class="tablesmall compact stripe" width="100%">';
  echo '<thead>';  
  echo '<tr>'; 
  echo '<th>'; 
  echo 'Name';    
  echo '</th>'; 
  echo '</tr>'; 
  echo '</thead>';       
  
  echo '<tbody>';   
  if (isset($arrInGroup) && isset($arrInGroup['Users'])) {
    foreach ($arrInGroup['Users'] as $strCurrStaffNumber => $strName) {
      echo '<tr>';
      echo '<td class="handcursor" onclick="javascript:RemoveFav(\''.$strCurrStaffNumber.'\','.$intID.')";>';
      echo $strName;
      echo '</td>';
      echo '</tr>';
    }
  }
  echo '</tbody>';  
  echo '</table>';
  echo '</table>';
  echo '</div>';  
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';
?>

<script type="text/javascript">
$(document).ready(function(){

  var table = $("#TableStaffAvailable").DataTable({
    paging: false,
    scrollY: 100,
    info:     false,
    stateSave: true,
    deferRender: true,
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]);

  var table = $("#TableStaffInGroup").DataTable({
    paging: false,
    scrollY: 100,
    info:     false,
    stateSave: true,
    deferRender: true,
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]);



  

  DoResize();   
})


function AddUser (staffnumber, id) {
  $.post("page-includes/users/user-group-add-to-group.php", {
     staffnumber: staffnumber,
     id: id
  },
  function(data,status){
    ShowFavourites(id);
    FillMenu();
  }
  )
}

function RemoveFav (staffnumber, id) {
  $.post("page-includes/users/user-group-remove-from-group.php", {
     staffnumber: staffnumber,
     id: id
  },
  function(data,status){
    ShowFavourites(id);
    FillMenu();
  }
  )
}

function DoResize() {
  var windowheight = $(window).height() - $("#myfavs").offset().top - 90;
  
  
  
  $('.dataTables_scrollBody').height((windowheight));
}


$(window).resize(function() {
  DoResize();
})
  
</script>

<?php
}
?>
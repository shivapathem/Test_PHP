<?php
session_start();

include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/supportfunctions.php';
// Get the 
$intDepartmentID = $_REQUEST['department'];

$arrattach = array('No','Internal','External');

$arrStaff = GetStaffSupport ($intDepartmentID);
$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);


//echo '<pre>';
//print_r($arrStaff);
//die;

  echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
  echo '<br>Staff Support for '.$strDepartmentName.'<br><br>';    
  echo '</div>';
        
  echo '<table class="tablesmall compact stripe" id="usersinfotable'.$intDepartmentID.'" width="100%">';
  echo '<thead>';
  echo '<tr>';  
  echo '<th nowrap>';  
  echo 'Name<br>'; 
  echo '</th>';   
  echo '<th>';    
  echo 'Staff Number<br>'; 
  echo '</th>';
  echo '<th>';    
  echo 'Login<br>'; 
  echo '</th>';  
  echo '<th>';    
  echo 'Designation<br>'; 
  echo '</th>';      
  echo '<th>';
  echo 'Manager<br>'; 
  echo '</th>';
  echo '<th>';    
  echo 'Appraiser<br>'; 
  echo '</th>';
  echo '<th>';    
  echo 'Mentor<br>'; 
  echo '</th>';
  echo '<th>';    
  echo 'Is Manager<br>'; 
  echo '</th>';
  echo '<th>';    
  echo 'Is Appraiser<br>'; 
  echo '</th>';
  echo '<th>';    
  echo 'Is Mentor<br>'; 
  echo '</th>';
  echo '<th>';    
  echo 'History/Info<br>'; 
  echo '</th>';  
      
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  if (isset($arrStaff)) {
  foreach ($arrStaff as $strStaffLogin => $arrStaffUser) {
    echo '<tr class="handcursor" ondblclick="javascript:UserAdminStaffSupportEdit(\''.$strStaffLogin.'\','.$intDepartmentID.');">';
    echo '<td nowrap>';
    echo $arrStaffUser['FullName'];
    echo '</td>';  
    echo '<td>';
    echo $arrStaffUser['StaffNumber'];  
    echo '</td>';
    echo '<td>';
    echo $strStaffLogin;  
    echo '</td>';    
    
    
    echo '<td>';
    echo $arrStaffUser['Designation'];  
    echo '</td>';
    echo '<td nowrap>';
    echo $arrStaffUser['ManagerFullName'];  
    echo '</td>';
    echo '<td nowrap>';
    echo $arrStaffUser['AppraiserFullName'];  
    echo '</td>';
    echo '<td nowrap>';
    echo $arrStaffUser['MentorFullName'];  
    echo '</td>';   
    echo '<td align="center">';
    echo '<span style="display:none">'.$arrStaffUser['isManager'].'</span>';
    if ($arrStaffUser['isManager'] == 1) {
      echo '<img onclick="javascript:EditUserAtributes(\''.$strStaffLogin.'\', 10, 0);" border="0" src="images/green_tick.png" width="12" height="12">';
    }
    else {
      echo '<img onclick="javascript:EditUserAtributes(\''.$strStaffLogin.'\', 10, 1);" border="0" src="images/red_cross.png" width="12" height="12">';
    }     
    echo '</td>';    
    echo '<td align="center">';
    echo '<span style="display:none">'.$arrStaffUser['isAppraiser'].'</span>';    
    if ($arrStaffUser['isAppraiser'] == 1) {
      echo '<img onclick="javascript:EditUserAtributes(\''.$strStaffLogin.'\', 1, 0);" border="0" src="images/green_tick.png" width="12" height="12">';
    }
    else {
      echo '<img onclick="javascript:EditUserAtributes(\''.$strStaffLogin.'\', 1, 1);" border="0" src="images/red_cross.png" width="12" height="12">';
    } 
    echo '</td>';    
    echo '<td align="center">';
    echo '<span style="display:none">'.$arrStaffUser['isMentor'].'</span>';
    if ($arrStaffUser['isMentor'] == 1) {
      echo '<img  onclick="javascript:EditUserAtributes(\''.$strStaffLogin.'\', 9, 0);" border="0" src="images/green_tick.png" width="12" height="12">';
    }
    else {
      echo '<img  onclick="javascript:EditUserAtributes(\''.$strStaffLogin.'\', 9, 1);" border="0" src="images/red_cross.png" width="12" height="12">';
    }  
    echo '</td>';    
      
    echo '<td align="center">';    
    ShowInfoHistory ($strStaffLogin);
    echo '</td>'; 
      
    echo '</tr>';
  }
  }
  echo '</tbody>';
  echo '</table>';
  
?>

<script type="text/javascript">
$(document).ready(function(){

    var table = $("#usersinfotable<?php echo $intDepartmentID?>").DataTable({
      paging: false,
      scrollY: 350, 
      info:     false,
      stateSave: true,
      "initComplete": function( settings, json ) {
      //DoResize();
      }
    });
    
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
    {column_number: 2,
      filter_type: 'text'
    },
    {column_number: 3,
      filter_type: 'text'
    },
    {column_number: 4,
      filter_type: 'text'
    },
    {column_number: 5,
      filter_type: 'text'
    },
    {column_number: 6,
      filter_type: 'text'
    },    

  ]);
	
  if ( $.cookie("usersinfotable") !== null ) {
    scrollPos = $.cookie("usersinfotable");
    $(".dataTables_scrollBody").scrollTop(scrollPos);
  }; 
    
});

$('.dataTables_scrollBody').on('scroll', function() { 
  $.cookie("usersinfotable", $(".dataTables_scrollBody").scrollTop());
});

  function DoResize() {
    var windowheight = $(window ).height() - 275;  
    $('.dataTables_scrollBody').height((windowheight));  
  }
  
function EditUserAtributes(LogIn, ActionType, Action) {
  $.post("page-includes/admin/deptschangestaffstatus.php", {
    login: LogIn,
    actiontype: ActionType,
    action: Action,
    department: <?php echo $intDepartmentID?>
  },
  function(data,status){
    GetContent(<?php echo $intDepartmentID?>)
   }
  )
}

</script>
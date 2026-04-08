<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';

$commonObj = new classCommonDBFunctions();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intOption =  $_REQUEST['option'] ?? 0;
// The group is the one we need to get
// The Option is what we need to retreive

$arrRequests = GetRequestsUnapproved($strUser);
switch ($intOption) {
   case 0:
      $RequestsOKwriteline='';
      // ##################################################################################  IsOK Requests 
      $RequestsOKwriteline.= '<table class="tablesmall compact stripe tablesmallW100  wordsBreak" id="RequestsOK">'; 
      $RequestsOKwriteline.= '<thead>';
      $RequestsOKwriteline.= '<tr>';
      $RequestsOKwriteline.= '<th class="cellWmm">';
      $RequestsOKwriteline.= 'Group';
      $RequestsOKwriteline.= '</th>'; 
      $RequestsOKwriteline.= '<th class="cellWmm">';
      $RequestsOKwriteline.= 'Type';
      $RequestsOKwriteline.= '</th>';             
      $RequestsOKwriteline.= '<th class="cellWmm">';
      $RequestsOKwriteline.= 'Date';
      $RequestsOKwriteline.= '</th>';   
      $RequestsOKwriteline.= '<th class="cellWmm">';
      $RequestsOKwriteline.='Week';
      $RequestsOKwriteline.='</th>';   
      $RequestsOKwriteline.= '<th class="cellWmm">';
      $RequestsOKwriteline.= 'Person';
      $RequestsOKwriteline.='</th>';  
      $RequestsOKwriteline.='<th class="cellWmmrequested">';
      $RequestsOKwriteline.='Requested';
      $RequestsOKwriteline.='</th>';     
      $RequestsOKwriteline.= '<th class="cellWmmcomments">';
      $RequestsOKwriteline.= 'Comments';
      $RequestsOKwriteline.= '</th>'; 
      $RequestsOKwriteline.= '</tr>'; 
      $RequestsOKwriteline.= '</thead>';
      $RequestsOKwriteline.= '<tbody>';      
      if (isset($arrRequests['LeaveRequests']['General'])) {    
        foreach ($arrRequests['LeaveRequests']['General'] as $strDate => $arrRequestsDates) {    
          foreach ($arrRequestsDates as $intRequestID => $arrRequest) {
            if ($arrRequest['IsOK'] == 1 && $arrRequest['Unlikely'] == 0) {
              $bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim($strDate);
              $intCurrWeek = $bbcweeknumberArray['ixYearWeek'] ?? '0';
              $intGroupID = $arrRequest['GroupID'];
              $RequestsOKwriteline.= '<tr class="handcursor" onclick="javascript:ShowWeeklyRequestsAdmin(\''.$strDate.'\',\''.$intGroupID.'\')">';
              $RequestsOKwriteline.= '<td>';
              $RequestsOKwriteline.= $arrRequest['GroupDescription'];
              $RequestsOKwriteline.= '</td>';
              
              $RequestsOKwriteline.= '<td>';
              $RequestsOKwriteline.=$arrRequest['TypeDescription'];
              $RequestsOKwriteline.='</td>';              

              $RequestsOKwriteline.='<td>';
              $RequestsOKwriteline.='<span class="customdateSort">';
              $RequestsOKwriteline.= date("Y-m-d", strtotime($strDate));
              $RequestsOKwriteline.='</span>';
              $RequestsOKwriteline.=date("l, jS M Y", strtotime($strDate));
              $RequestsOKwriteline.='</td>';    
       
              $RequestsOKwriteline.='<td>';
              $RequestsOKwriteline.= spinweek($intCurrWeek);
              $RequestsOKwriteline.='</td>';
              
              $RequestsOKwriteline.='<td>';
              $RequestsOKwriteline.= $arrRequest['FullName'];
              $RequestsOKwriteline.= '</td>';

              $RequestsOKwriteline.='<td>';
              $RequestsOKwriteline.='<span class="customdateSort">';
              $RequestsOKwriteline.= date("Y-m-d", strtotime($arrRequest['Created']));
              $RequestsOKwriteline.='</span>';
              $RequestsOKwriteline.=date('jS F Y H:i', strtotime($arrRequest['Created']));
              $RequestsOKwriteline.='</td>';

              $RequestsOKwriteline.='<td>';
              if ($arrRequest['UserComments'] != '') {
                $RequestsOKwriteline.='<b>User Comments</b><br>';
                $RequestsOKwriteline.= $arrRequest['UserComments']; 
                $RequestsOKwriteline.='<br/>';             
              }
              if ($arrRequest['Comments'] != '') {
                $RequestsOKwriteline.='<b>Office Comments</b><br>';
                $RequestsOKwriteline.= $arrRequest['Comments'];              
              }              
              $RequestsOKwriteline.='</td>'; 
              $RequestsOKwriteline.='</tr>';
            }
          }   
        }
      }
      $RequestsOKwriteline.='</tbody>';
      $RequestsOKwriteline.='</table>'; 
      echo $RequestsOKwriteline;   
?>      
<script type="text/javascript">
$(document).ready(function(){
  var table = $("#RequestsOK").DataTable({
    paging: false,
    scrollY: 1000,
    info:     false,
    stateSave: true,
    deferRender: true,
    scrollCollapse: true,
    "initComplete": function( settings, json ) {
      if ($("#RequestsOK").length) {
       ResizeRequestsOKGrid();
      }
    }
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'select'
    },
    {column_number: 1,
      filter_type: 'text'
    },
    {column_number: 3,
      filter_type: 'text'
    },
    {column_number: 4,
      filter_type: 'text'
    },
  ]);
})
function ResizeRequestsOKGrid () {
  if ($("#RequestsOK").length) {
    var offset = parseInt($("#RequestsOK").offset().top);
    var windowheight = $(window).height() - offset - 30;
    $('.dataTables_scrollBody:has(#RequestsOK)').height(windowheight+'px');
    $(".dataTables_scrollBody").css({ "overflow-y": "hidden","max-height":"365px" });
    $('#RequestsOK').dataTable().fnAdjustColumnSizing();
  }
}
$(window).resize(function() {
  if ($("#RequestsOK").length) {
    ResizeRequestsOKGrid();
  }
})     
</script>    
<?php        
    break;
    case 1:
      $RequestsNotOKwriteline='';
      // ##################################################################################  Is NOT OK Requests  
      $RequestsNotOKwriteline.= '<table class="tablesmall compact stripe wordsBreak" id="RequestsNotOK">'; 
      $RequestsNotOKwriteline.='<thead>';
      $RequestsNotOKwriteline.='<tr>';
      $RequestsNotOKwriteline.='<th class="mw">';
      $RequestsNotOKwriteline.='Group';
      $RequestsNotOKwriteline.='</th>'; 
      $RequestsNotOKwriteline.='<th class="mw">';
      $RequestsNotOKwriteline.='Type';
      $RequestsNotOKwriteline.='</th>';             
      $RequestsNotOKwriteline.='<th class="mw">';
      $RequestsNotOKwriteline.='Date';
      $RequestsNotOKwriteline.='</th>';   
      $RequestsNotOKwriteline.='<th class="mw">';
      $RequestsNotOKwriteline.='Week';
      $RequestsNotOKwriteline.='</th>';   
      $RequestsNotOKwriteline.='<th class="mw">';
      $RequestsNotOKwriteline.='Person';
      $RequestsNotOKwriteline.='</th>';  
      $RequestsNotOKwriteline.='<th class="cellWmmrequested">';
      $RequestsNotOKwriteline.='Requested';
      $RequestsNotOKwriteline.='</th>';  
      $RequestsNotOKwriteline.='<th class="cellWmmcomments">';
      $RequestsNotOKwriteline.='Comments';
      $RequestsNotOKwriteline.='</th>';          
      $RequestsNotOKwriteline.= '</tr>'; 
      $RequestsNotOKwriteline.= '</thead>';
      $RequestsNotOKwriteline.='<tbody>';    
      if (isset($arrRequests['LeaveRequests']['General'])) {      
        foreach ($arrRequests['LeaveRequests']['General'] as $strDate => $arrRequestsDates) {    
          foreach ($arrRequestsDates as $intRequestID => $arrRequest) {
            if ($arrRequest['IsOK'] == 0 && $arrRequest['Unlikely'] == 0) {
              $intCurrWeek = bbcweeknumber($strDate);
              $intGroupID = $arrRequest['GroupID'];
              $RequestsNotOKwriteline.='<tr class="handcursor" onclick="javascript:ShowWeeklyRequestsAdmin(\''.$strDate.'\',\''.$intGroupID.'\')">';
              $RequestsNotOKwriteline.= '<td>';
              $RequestsNotOKwriteline.=$arrRequest['GroupDescription'];
              $RequestsNotOKwriteline.= '</td>';
              
              $RequestsNotOKwriteline.='<td>';
              $RequestsNotOKwriteline.= $arrRequest['TypeDescription'];
              $RequestsNotOKwriteline.='</td>';              

              $RequestsNotOKwriteline.='<td>';
              $RequestsNotOKwriteline.='<span class="customdateSort">';
              $RequestsNotOKwriteline.= date("Y-m-d", strtotime($strDate));
              $RequestsNotOKwriteline.='</span>';
              $RequestsNotOKwriteline.= date("l, jS M Y", strtotime($strDate));
              $RequestsNotOKwriteline.='</td>';    
       
              $RequestsNotOKwriteline.='<td>';
              $RequestsNotOKwriteline.= spinweek($intCurrWeek);
              $RequestsNotOKwriteline.= '</td>';
              
              $RequestsNotOKwriteline.='<td>';
              $RequestsNotOKwriteline.=$arrRequest['FullName'];
              $RequestsNotOKwriteline.='</td>';

              $RequestsNotOKwriteline.= '<td>';
              $RequestsNotOKwriteline.='<span class="customdateSort">';
              $RequestsNotOKwriteline.= date("Y-m-d", strtotime($arrRequest['Created']));
              $RequestsNotOKwriteline.='</span>';
              $RequestsNotOKwriteline.= date('jS F Y H:i', strtotime($arrRequest['Created']));
              $RequestsNotOKwriteline.= '</td>';

              $RequestsNotOKwriteline.='<td>';
              if ($arrRequest['UserComments'] != '') {
                $RequestsNotOKwriteline.= '<b>User Comments</b><br>';
                $RequestsNotOKwriteline.= $arrRequest['UserComments'];
                $RequestsNotOKwriteline.='<br/>';
              }
              if ($arrRequest['Comments'] != '') {
                $RequestsNotOKwriteline.='<b>Office Comments</b><br>';
                $RequestsNotOKwriteline.=$arrRequest['Comments'];
              }              
              $RequestsNotOKwriteline.='</td>'; 
              $RequestsNotOKwriteline.='</tr>';
            }
          }   
        }
      }
      $RequestsNotOKwriteline.= '</tbody>';  
      $RequestsNotOKwriteline.= '</table>';
      echo $RequestsNotOKwriteline;
?>      
<script type="text/javascript">
$ (document).ready(function() {
  var table = $("#RequestsNotOK").DataTable({
    paging: false,
    scrollY: 1000,
    info:     false,
    stateSave: true,
    deferRender: true,
    scrollCollapse: true,
    "initComplete": function( settings, json ) {
      if ($("#RequestsNotOK").length) {
        ResizeRequestsNotOKGrid();
      }
    }
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'select'
    },
    {column_number: 1,
      filter_type: 'text'
    },
    {column_number: 3,
      filter_type: 'text'
    },
    {column_number: 4,
      filter_type: 'text'
    },    
  ]);
})    
function ResizeRequestsNotOKGrid () {
  if ($("#RequestsNotOK").length) {
    var offset = parseInt($("#RequestsNotOK").offset().top);
    var windowheight = $(window).height() - offset - 30;
    $('.dataTables_scrollBody:has(#RequestsNotOK)').height(windowheight+'px');
    $(".dataTables_scrollBody").css({ "overflow-y": "hidden","max-height":"365px" });
    $('#RequestsNotOK').dataTable().fnAdjustColumnSizing();
  }
}
$(window).resize(function() {
  if ($("#RequestsNotOK").length) {
    ResizeRequestsNotOKGrid();
  }
})
</script>    
<?php           
      // ##################################################################################  END Is NOT OK Leave   
    break;  
    case 2:
      $writeline='';
    // ##################################################################################  Is marked Unlikely 
    // ##################################################################################  Do the ShortNotice
      $writeline.= '<table class="tablesmall compact stripe requesttextbreck wordsBreak" id="RequestsUnlikely">'; 
      $writeline.= '<thead>';
      $writeline.= '<tr>';
      $writeline.= '<th class="mw">';
      $writeline.= 'Group';
      $writeline.= '</th>'; 
      $writeline.= '<th class="mw">';
      $writeline.= 'Type';
      $writeline.= '</th>';             
      $writeline.= '<th class="mw">';
      $writeline.= 'Date';
      $writeline.= '</th>';   
      $writeline.= '<th class="mw">';
      $writeline.= 'Week';
      $writeline.= '</th>';   
      $writeline.= '<th class="mw">';
      $writeline.= 'Person';
      $writeline.= '</th>';  
      $writeline.= '<th class="cellWmmrequested">';
      $writeline.= 'Requested';
      $writeline.= '</th>';     
      $writeline.= '<th class="mwcomments">';
      $writeline.= 'Comments';
      $writeline.= '</th>'; 
      $writeline.= '<th class="mwOk">';
      $writeline.= 'Is OK';
      $writeline.= '</th>';
      $writeline.= '</tr>'; 
      $writeline.= '</thead>';
      $writeline.= '<tbody>';   
    if (isset($arrRequests['LeaveRequests']['ShortNotice'])) { 
      foreach ($arrRequests['LeaveRequests']['ShortNotice'] as $strDate => $arrRequestsDates) {    
        foreach ($arrRequestsDates as $intRequestID => $arrRequest) { 
          if ($arrRequest['Approved'] == 0 && $arrRequest['Unlikely'] == 1) {
              $intCurrWeek = bbcweeknumber($strDate);
              $intGroupID = $arrRequest['GroupID'];
              $writeline.= '<tr class="handcursor" onclick="javascript:ShowWeeklyRequestsAdmin(\''.$strDate.'\',\''.$intGroupID.'\')">';
              $writeline.= '<td>';
              $writeline.= $arrRequest['GroupDescription'];
              $writeline.= '</td>';
              
              $writeline.= '<td>';
              $writeline.= $arrRequest['TypeDescription'];
              $writeline.= '</td>';              

              $writeline.= '<td>';
              $writeline.='<span class="customdateSort">';
              $writeline.= date("Y-m-d", strtotime($strDate));
              $writeline.='</span>';
              $writeline.= date("l, jS M Y", strtotime($strDate));
              $writeline.= '</td>';    
       
              $writeline.= '<td>';
              $writeline.= spinweek($intCurrWeek);
              $writeline.= '</td>';
              
              $writeline.= '<td>';
              $writeline.= $arrRequest['FullName'];
              $writeline.= '</td>';

              $writeline.= '<td>';
              $writeline.='<span class="customdateSort">';
              $writeline.= date("Y-m-d", strtotime($arrRequest['Created']));
              $writeline.='</span>';
              $writeline.= date('jS F Y H:i', strtotime($arrRequest['Created']));
              $writeline.= '</td>';               

              $writeline.= '<td>';
              if ($arrRequest['UserComments'] != '') {
                $writeline.= '<b>User Comments</b><br>';
                $writeline.= $arrRequest['UserComments'];
                $writeline.='<br/>';              
              }
              if ($arrRequest['Comments'] != '') {
                $writeline.= '<b>Office Comments</b><br>';
                $writeline.= $arrRequest['Comments'];              
              }              
              $writeline.= '</td>'; 
              $writeline.= '<td class="LeaveShortNoticeApplied">';
              $writeline.= '</td>';                           
              $writeline.= '</tr>';
          }
        }
      }   
    }
    // ##################################################################################  END Do the ShortNotice  
      if (isset($arrRequests['LeaveRequests']['General'])) {      
        foreach ($arrRequests['LeaveRequests']['General'] as $strDate => $arrRequestsDates) {    
          foreach ($arrRequestsDates as $intRequestID => $arrRequest) {
            if ($arrRequest['Unlikely'] == 1) {
              $intCurrWeek = bbcweeknumber($strDate);
              $intGroupID = $arrRequest['GroupID'];
              $writeline.= '<tr class="handcursor" onclick="javascript:ShowWeeklyRequestsAdmin(\''.$strDate.'\',\''.$intGroupID.'\')">';
              $writeline.= '<td>';
              $writeline.= $arrRequest['GroupDescription'];
              $writeline.= '</td>';
              
              $writeline.= '<td>';
              $writeline.= $arrRequest['TypeDescription'];
              $writeline.= '</td>';              

              $writeline.= '<td>';
              $writeline.='<span class="customdateSort">';
              $writeline.= date("Y-m-d", strtotime($strDate));
              $writeline.='</span>';
              $writeline.= date("l, jS M Y", strtotime($strDate));
              $writeline.= '</td>';    
       
              $writeline.= '<td>';
              $writeline.= spinweek($intCurrWeek);
              $writeline.= '</td>';
              
              $writeline.= '<td>';
              $writeline.= $arrRequest['FullName'];
              $writeline.= '</td>';

              $writeline.= '<td>';
              $writeline.='<span class="customdateSort">';
              $writeline.= date("Y-m-d", strtotime($arrRequest['Created']));
              $writeline.='</span>';
              $writeline.= date('jS F Y H:i', strtotime($arrRequest['Created']));
              $writeline.= '</td>';               

              $writeline.= '<td>';
              if ($arrRequest['UserComments'] != '') {
                $writeline.= '<b>User Comments</b><br>';
                $writeline.= $arrRequest['UserComments'];
                $writeline.='<br/>';             
              }
              if ($arrRequest['Comments'] != '') {
                $writeline.= '<b>Office Comments</b><br>';
                $writeline.= $arrRequest['Comments'];              
              }              
              $writeline.= '</td>'; 
              $writeline.= '<td align="center">'; 
              $writeline.='<span class="customdateSort">';
              $writeline.= $arrRequest['IsOK'];
              $writeline.='</span>';
              if ($arrRequest['IsOK'] == 1) {
                $writeline.= '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';  
              }
              else {
                $writeline.= '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';  
              }
              $writeline.= '</td>';    
              $writeline.= '</tr>';
            }
          }   
        }
      }
      $writeline.= '</table>';
      echo $writeline; 
      
      
          ?>
      <script type="text/javascript">
$(document).ready(function(){

  var table = $("#RequestsUnlikely").DataTable({
    paging: false,
    scrollY: 1000,
    info:     false,
    stateSave: true,
    deferRender: true,
    scrollCollapse: true,
    "initComplete": function( settings, json ) {
      if ($("#RequestsUnlikely").length) {
        ResizeRequestsUnlikelyGrid();
      }
    }
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'select'
    },
    {column_number: 1,
      filter_type: 'text'
    },
    {column_number: 3,
      filter_type: 'text'
    },
    {column_number: 4,
      filter_type: 'text'
    },    
  ]);
})
function ResizeRequestsUnlikelyGrid () {
  if ($("#RequestsUnlikely").length) {
    var offset = parseInt($("#RequestsUnlikely").offset().top);
    var windowheight = $(window).height() - offset - 30;
    $('.dataTables_scrollBody:has(#RequestsUnlikely)').height(windowheight+'px');
    $(".dataTables_scrollBody").css({ "overflow-y": "hidden","max-height":"365px"});
    $('#RequestsUnlikely').dataTable().fnAdjustColumnSizing();
  }
}
$(window).resize(function() {
  if ($("#RequestsUnlikely").length) {
    ResizeRequestsUnlikelyGrid();
  }
})    
</script>    
<?php      
 // ##################################################################################  END Is NOT OK Leave   
 break;      
}

?>


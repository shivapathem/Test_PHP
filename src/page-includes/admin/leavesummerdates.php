<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
  }
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
if (isset($_REQUEST['year'])) {
  $year = $_REQUEST['year'];
}
else {
  if (isset($_REQUEST['date'])) {
    $year = date ("Y", strtotime("-3 months", strtotime($_REQUEST['date'])));
  }
  else {
    $year = date("Y", strtotime("-3 months"));
  }
}
$intLeaveGroupID = $_REQUEST['id'];
$intAdminLevel = GetAdminLeaveRequestGroupsAdminFromLogin ($strUser, $intLeaveGroupID);

$startofyear = "01 April ".$year;
$endofyear = "31 March ".($year + 1);
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

if (isset($_REQUEST['submit'])) {
    $startdate = date ("Y-m-d ", strtotime($_REQUEST['SummerStart']));
    $enddate = date ("Y-m-d ", strtotime($_REQUEST['SummerEnd']));
    $untildate = date ("Y-m-d ", strtotime($_REQUEST['SummerCutOff']));
    $amount = $_REQUEST['MaxSummerRequests'];
    //insert-update summer leave

    insUpdSummerLeave($year,$intLeaveGroupID,$startdate,$enddate,$untildate,$amount,$sessUserId);
}
else {
  $strLeaveGroupDesc = GetLeaveRequestDescFromID($intLeaveGroupID);

  $summerdates =GetSummerLeaveDetail($intLeaveGroupID,$year);
  
  if (!empty($summerdates)) {
   
    $startdatetext = $startdate =  date("d F Y", strtotime($summerdates['dStart']));  
    $enddatetext = $enddate =  date ("d F Y", strtotime($summerdates['dEnd']));
    $untildatetext = $untildate =  date ("d F Y", strtotime($summerdates['dUntil']));
    $amount = $summerdates['amount'];
  }
  else {
    
    $startdate = date("d F Y", strtotime($year.'-04-01'));
    $enddate = date("d F Y", strtotime($year.'-04-01'));
    $untildate = date("d F Y", strtotime($year.'-04-01'));
    $startdatetext = '';
    $enddatetext = '';
    $untildatetext = '';
    $amount = '';

  }
  
  echo '<div id="summertab">';
  echo '<table border="0" width="800px" class="tablesmall">';
  echo '<tr>';
  echo '<td class="lightcell medtextbold"><br>Summer Leave Dates for '.$year.'<br>'.$strLeaveGroupDesc.'<br><br></td>';
  echo '<td nowrap class="lightcell smalltext handcursor"width="100px" onclick="javascript:ShowSummerLeave('.$intLeaveGroupID.', '.($year - 1).')";>&nbsp;&lt;&lt; Year '.($year - 1).'</td>';
  echo '<td nowrap class="lightcell smalltext" width="50px"></td>';
  echo '<td nowrap align="right" class="lightcell smalltext handcursor" width="100px" onclick="javascript:ShowSummerLeave('.$intLeaveGroupID.', '.($year + 1).')";>Year '.($year + 1).'&gt;&gt;&nbsp;</td>';
  echo '</tr>';
  echo '</table>';
  echo '<br>';
  if ($intAdminLevel == 2) {
    echo '<form id="summer-form">';
    echo '<table cellspadding="2px" class="tablesmall" cellspacing="4px" border="0" width="800px">';
    echo '<tr>';
    echo '<td colspan="2" class="tableheadersmall smalltextbold" width="100%">&nbsp;</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightcell smalltext" valign="top" width="50%">Summer Leave Starts<br>';
    echo '<br>';
    echo '<input class="smalltext" autocomplete="off" name="SummerStart" id="datepicker" size="20" value="'.$startdatetext.'" type="text">';
    echo '</td>';
    echo '<td class="lightcell smalltext">Summer Leave Ends<br>';
    echo '<br>';
    echo '<input class="smalltext" autocomplete="off"  name="SummerEnd" id="datepicker1" size="20" value="'.$enddatetext.'" type="text">';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightcell smalltext" valign="top" width="50%">Summer Restriction Lifted<br>';
    echo '<br>';
    echo '<input class="smalltext" autocomplete="off" name="SummerCutOff" id="datepicker2" size="20" value="'.$untildatetext.'" type="text">';
    echo '</td>';
    echo '<td class="lightcell smalltext" valign="top" width="50%">Max Summer Requests<br>';
    echo '<br>';
    echo '<input class="smalltext" autocomplete="off" name="MaxSummerRequests" id="MaxRequests" size="20" value="'.$amount.'" type="text">';
    echo '</td>';
    echo '</tr> ';
    echo '<tr>';
    echo '<td class="lightcell">&nbsp;</td>';
    echo '<td class="lightcell" align="center"><input type="submit" value="Update" name="submit"></td>';
    echo '</tr>';
    echo '</table>';
    echo '<input type="hidden" name="year" value="'.$year.'">';
    echo '<input type="hidden" name="id" value="'.$intLeaveGroupID.'">';
    echo '</form>';
  }
  else {
    echo '<table cellspadding="2px" class="tablesmall" cellspacing="4px" border="0" width="800px">';
    echo '<tr>';
    echo '<td colspan="2" class="tableheadersmall smalltextbold" width="100%">&nbsp;</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="lightcell smalltext" valign="top" width="50%">Summer Leave Starts<br>';
    echo $startdatetext;
    echo '</td>';
    echo '<td class="lightcell smalltext">Summer Leave Ends<br>';
    echo $enddatetext;
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="2" class="tableheadersmall smalltextbold" width="100%">&nbsp;</td>';
    echo '</tr>';    
    echo '<tr>';
    echo '<td class="lightcell smalltext" valign="top" width="50%">Summer Restriction Lifted<br>';
    echo $untildatetext;
    echo '</td>';
    echo '<td class="lightcell smalltext" valign="top" width="50%">Max Summer Requests<br>';
    echo $amount;
    echo '</td>';
    echo '</tr> ';
    echo '</table>';
  
  }
  
  echo '</div>';

?>

<script language="JavaScript" type="text/javascript">
$.validator.addMethod("greaterThan", 
function(value, element, params) {

    if (!/Invalid|NaN/.test(new Date(value))) {
        return new Date(value) > new Date($(params).val());
    }

    return isNaN(value) && isNaN($(params).val()) 
        || (Number(value) > Number($(params).val())); 
},'Cannot be less than Leave Starts.');
$('#summer-form').validate({
  rules: {
    SummerStart: {
      required: true,
      date: true
    },
    SummerEnd: {
      required: true,
      date: true,
      greaterThan: '#datepicker'
    },
    SummerCutOff: {
      required: true,
      date: true
    },
    MaxSummerRequests: {
      required: true
    }
  },
  submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
    $.ajax({type:'POST', url: 'page-includes/admin/leavesummerdates.php', data:$('#summer-form').serialize(), success: function(data) {
      $.facebox.close();
        ShowSummerLeave(<?php echo $intLeaveGroupID?>, <?php echo $year?>);            
    }});
  }   
})

  $(function() {
    $( "#datepicker" ).datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd MM yy',
      firstDay: '6',
      defaultDate: '<?php echo $startdatetext?>',
      minDate: '<?php echo $startofyear?>',
      maxDate: '<?php echo $endofyear?>'
    });
  });
  $(function() {
    $( "#datepicker1" ).datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'dd MM yy',
      firstDay: '6',
      defaultDate: '<?php echo $enddatetext?>',
      minDate: '<?php echo $startofyear?>',
      maxDate: '<?php echo $endofyear?>'
    });
  });
  $(function() {
    $( "#datepicker2" ).datepicker({
      changeMonth: true,
      changeYear: true,
      firstDay: '6',
      dateFormat: 'dd MM yy',
      defaultDate: '<?php echo $untildatetext?>',
      minDate: '<?php echo $startofyear?>',
      maxDate: '<?php echo $endofyear?>'
    });
  });

</script>

<?php
}
?>
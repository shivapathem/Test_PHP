<?php
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/genericfunctions.php';

$pdo = OpenDBLinkA7();
if (isset($_REQUEST['clear'])) {
  try {
    $casevar ='DELETE';
    $rowID =null;
    $strStartTime ='';
    $strEndTime='';
    $strQuery = "exec [dbo].[usp_SystestemDownTime] :casevar ,:rowID ,:strStartTime, :strEndTime";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':casevar', $casevar, PDO::PARAM_STR);
    $stmt->bindParam(':rowID', $rowID, PDO::PARAM_INT);
    $stmt->bindParam(':strStartTime', $strStartTime, PDO::PARAM_STR);
    $stmt->bindParam(':strEndTime', $strEndTime, PDO::PARAM_STR);
    $stmt->execute();
  } catch (Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
} else {
  //Fetch if Already Exist
  $casevar ='FETCH';
  $rowID =null;
  $strStartTime ='';
  $strEndTime='';
    try {
      $strQuery = "exec [dbo].[usp_SystestemDownTime] :casevar ,:rowID ,:strStartTime, :strEndTime";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(':casevar', $casevar, PDO::PARAM_STR);
      $stmt->bindParam(':rowID', $rowID, PDO::PARAM_INT);
      $stmt->bindParam(':strStartTime', $strStartTime, PDO::PARAM_STR);
      $stmt->bindParam(':strEndTime', $strEndTime, PDO::PARAM_STR);
      $stmt->execute();
      $rsDown = $stmt->fetch(PDO::FETCH_ASSOC);
      $ID = $rsDown['ID'] ?? 0;
    } catch (Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }
   
  //If saved infor
  if (isset($_REQUEST['submit'])) {
    $strStartTime = $_REQUEST['sDate'].' '.$_REQUEST['StartTime'];
    $strEndTime = $_REQUEST['eDate'].' '.$_REQUEST['EndTime'];
    if (empty($rsDown)) {
      try {
        $casevar = 'INSERT';
        $rowID = null;
        $strQuery = "exec [dbo].[usp_SystestemDownTime] :casevar ,:rowID ,:strStartTime, :strEndTime";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(':casevar', $casevar, PDO::PARAM_STR);
        $stmt->bindParam(':rowID', $rowID, PDO::PARAM_INT);
        $stmt->bindParam(':strStartTime', $strStartTime, PDO::PARAM_STR);
        $stmt->bindParam(':strEndTime', $strEndTime, PDO::PARAM_STR);
        $stmt->execute();
      } catch (Exception $e) {
        logger()->critical('DB Error', (array) $e);
      }
      $strProperty='';
    } else {
        try {
        $casevar = 'UPDATE';
        $rowID = $ID;
         $strQuery = "exec [dbo].[usp_SystestemDownTime] :casevar ,:rowID ,:strStartTime, :strEndTime";
          $stmt = $pdo->prepare($strQuery);
          $stmt->bindParam(':casevar', $casevar, PDO::PARAM_STR);
          $stmt->bindParam(':rowID', $rowID, PDO::PARAM_INT);
          $stmt->bindParam(':strStartTime', $strStartTime, PDO::PARAM_STR);
          $stmt->bindParam(':strEndTime', $strEndTime, PDO::PARAM_STR);
          $stmt->execute();
        } catch (Exception $e) {
          logger()->critical('DB Error', (array) $e);
        }
    }
  }
  
  if (!empty($rsDown)) {
     $strProperty = '';
     $strStartTime = date('H:i', strtotime($rsDown['StartTime']));
     $strEndTime = date('H:i', strtotime($rsDown['EndTime']));
     $strStartDate = date('Y-m-d', strtotime($rsDown['StartTime']));
     $strEndDate = date('Y-m-d', strtotime($rsDown['EndTime']));
     $strFullStartDate = date('l, j F Y', strtotime($rsDown['StartTime']));
     $strFullEndDate = date('l, j F Y', strtotime($rsDown['EndTime']));
  } else {
    $strProperty = ' disabled';
    $strStartTime = "";
    $strEndTime = "";
    $strStartDate = "";
    $strEndDate = "";
    $strFullStartDate = "";
    $strFullEndDate = "";
  }

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
echo '<br><h2 aria-label="System Down Time">System Down Time.</h2>You can make this site unavailable by entering dates and times below.<br><br>';
echo '</div>';
echo '<br>';
echo '<form id="downtime">';
echo '<table class="tablesmalltidy" width="100%">';
echo '<tr>';
echo '<th valign="top" width="150px">';
echo 'Start Date';
echo '</th>';
echo '<td width="50px" align="right"><input type="hidden" name="sDate" id="datepicker-start" value="'.$strEndDate.'" required/></td>';
echo '<td width="150px"><input type="text" name="salternate" id="salternate" size="30" value="'.$strFullStartDate.'"></td>';
echo '<th valign="top" width="150px">';
echo 'Start Time';
echo '</th>';
echo '<td>';
echo '<input id="StartTime" name="StartTime" type="text" class="time" size="10" value="'.$strStartTime.'" onchange="setStartTime();" onblur="setStartTime();"  onClick="setStartTime();"/>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<th valign="top" width="150px">';
echo 'End Date';
echo '</th>';
echo '<td width="50px" align="right"><input type="hidden" name="eDate" id="datepicker-end" value="'.$strEndDate.'" required/></td>';
echo '<td width="150px"><input type="text" name="ealternate" id="ealternate" size="30" value="'.$strFullEndDate.'"></td>';
echo '<th valign="top" width="150px">';
echo 'End Time';
echo '</th>';
echo '<td>';
echo '<input id="EndTime" name="EndTime" type="text" class="time" size="10" value="'.$strEndTime.'" onchange="setEndTime();" onblur="setEndTime();" onClick="setEndTime();"/>';
echo '</td>';
echo '</tr>';

echo '<tr height="30px">';
echo '<td>';
echo '</td>';
echo '<td colspan="4">';
echo '<input name="submit" type="submit" value="Apply">';
echo '&nbsp;&nbsp;<input type="button" id="Cancel" value="Remove Restriction" onclick="RemoveRestriction()"'.$strProperty.'>';

echo '</td>';
echo '</tr>';

echo '</table>';
echo '</form>';
?>

<script type="text/javascript">
$('document').ready(function(){
  $('#downtime').validate({
      rules:{
        "StartTime":{
          required:true
        },
        "EndTime":{
          required:true
        },
        "salternate":{
          required:true,
          date: true
        },
        "ealternate":{
          required:true,
          date: true
        }
      },
    submitHandler: function(form) {
        $.ajax({type:'POST', url: 'page-includes/admin/system-down-time.php', data:$('#downtime').serialize(), success: function(data) {
          $('#systemoptiontabs-7').html(data);
        }});   
    }
  })

  $(function() {
    $('#StartTime').timepicker({
      'step': 15,
      'timeFormat': 'H:i'
      });
  });
  $(function() {
    $('#EndTime').timepicker({
      'step': 15,
      'timeFormat': 'H:i'
      });
  });
  $(function() {
    var d= new Date();
    var startYR = d.getFullYear();
    var startMonth = d.getMonth()+1;
    $( "#datepicker-start" ).datepicker({
      changeYear: 'true',
      changeMonth: 'true',
      firstDay: '6',
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      defaultDate: "<?php echo $strStartDate?>" ,
      dateFormat: 'yy-mm-dd',
      altField: "#salternate",
      altFormat: "DD, d MM, yy",
      minDate: 0
    }).change(function (selected) {
         var sdate = $('#datepicker-start').val();
          $("#datepicker-end").datepicker({
            changeYear: 'true',
            changeMonth: 'true',
            firstDay: '6',
            showOn: "button",
            buttonImage: "images/calendar.gif",
            buttonImageOnly: true,
            defaultDate: "<?php echo $strEndDate?>" ,
            dateFormat: 'yy-mm-dd',
            altField: "#ealternate",
            altFormat: "DD, d MM, yy",
            minDate: sdate
          });
  
    });
  });
  $(function() {
    var sdate = $('#datepicker-start').val();
    if(sdate==undefined || sdate=='') {
      sdate=0;
    }
    $( "#datepicker-end" ).datepicker({
      changeYear: 'true',
      changeMonth: 'true',
      firstDay: '6',
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      defaultDate: "<?php echo $strEndDate?>" ,
      dateFormat: 'yy-mm-dd',
      altField: "#ealternate",
      altFormat: "DD, d MM, yy",
      minDate: sdate
    });
  });


});

function RemoveRestriction() {
  $.post("page-includes/admin/system-down-time.php", {
    clear: 1
  },
  function(data,status){
     ShowDownTime();
   }
  )
}

function setStartTime() {
        var x = $("#StartTime").val();
        setTimeout(function () {
            if (x.length =='' || x==0) {
                $("#StartTime").val('00:00');
            }
        }, 100);
    }
    function setEndTime() {
        var y = $("#EndTime").val();
        setTimeout(function () {
            if (y.length =='' || y==0) {
                $("#EndTime").val('00:00');
            }
        }, 100);
    }
</script>

<?php
}
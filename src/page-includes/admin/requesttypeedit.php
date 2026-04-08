<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/helpers.php';
$pdo = OpenDBLinkA7();
$id = $_REQUEST['id'];
$intGroupID = $_REQUEST['groupid'];

if (isset($_REQUEST['Update'])) {
  $id = $_REQUEST['id'];
  $intGroupID = $_REQUEST['groupid'];
  $description = trim($_REQUEST['description']);
  $sdate = $_REQUEST['sDate'];
  $edate = $_REQUEST['eDate'];
  $starts = $_REQUEST['starts'];
  $ends = $_REQUEST['ends'];
  $day[0] = $_REQUEST['0'];
  $day[1] = $_REQUEST['1'];
  $day[2] = $_REQUEST['2'];
  $day[3] = $_REQUEST['3'];
  $day[4] = $_REQUEST['4'];
  $day[5] = $_REQUEST['5'];
  $day[6] = $_REQUEST['6'];
  $NumberAllowed = $_REQUEST['NumberAllowed'];
  if (isset($_REQUEST['isRestricted'])) {
    $isRestricted = 1;
  } else {
    $isRestricted = 0;
  }

  if (isset($_REQUEST['SendEmails'])) {
    $intSendEmails = 1;
  } else {
    $intSendEmails = 0;
  }  
  
  if (isset($_REQUEST['UniqueCount'])) {
    $UniqueCount = 1;
  } else {
    $UniqueCount = 0;
  }

  if (isset($_REQUEST['AllowOverLimit'])) {
    $AllowOverLimit = 1;
  } else {
    $AllowOverLimit = 0;
  }
  if (isset($_REQUEST['affectlocks'])) {
    $affectlocks = 1;
  } else {
    $affectlocks = 0;
  }
  if (isset($_REQUEST['AffectsOthers'])) {
    $AffectsOthers = 1;
  } else {
    $AffectsOthers = 0;
  }
 
 if ($description!='') {
    $strQuery = "exec [dbo].[USP_INSERT_AND_UPDATE_REQUEST_TYPE] '".$id."','".$description."','".$sdate."','".$edate."','".$day[0]."','".$day[1]."', '".$day[2]."', '".$day[3]."','".$day[4]."','".$day[5]."','".$day[6]."','".$intGroupID."','".$isRestricted."','".$UniqueCount."','".$NumberAllowed."','".$starts."','".$AllowOverLimit."','".$affectlocks."','".$AffectsOthers."','".$ends."','".$intSendEmails."'";
      try {
        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
      } catch(Exception $e) {
        logger()->critical('DB Error', (array) $e);
      }                  
    }
}
else {
  $strLeaveGroupDesc = GetLeaveRequestDescFromID ($intGroupID);
  if ($id !=0) {
    // Get the first and last requests already in
        try {
          $query = "SELECT MIN(dDate) AS MinDate, MAX(dDate) AS MaxDate
              FROM  Requests (NOLOCK)
              WHERE (RequestType = :id)
              AND (Deleted = 0)";
          $stmt = $pdo->prepare($query);
          $stmt->bindParam(':id', $id, PDO::PARAM_INT);
          $stmt->execute();
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
          logger()->critical('DB Error', (array) $e);
        }  
    if (!is_null($row['MinDate'])) {
      $minDate = date('Y-m-d',strtotime($row['MinDate']));
      $strMinDate = date('jS F Y',strtotime($row['MinDate']));      
      $maxDate = date('Y-m-d',strtotime($row['MaxDate']));
      $strMaxDate = date('jS F Y',strtotime($row['MaxDate']));
      $intHasRequests = 1;
    }
    else {
      $minDate = 0;
      $maxDate = 0;
      $intHasRequests = 0;
    }
    try {
      $query = "SELECT id, description, startdate, enddate, day_0, day_1, day_2, day_3, day_4, day_5, day_6, GroupID, isRestricted,
              UniqueCount, RequestsAllowed, Starts, Ends, AllowOverLimit, AffectLocks, AffectsOthers, SendEmails
              FROM  RequestTypes (NOLOCK)
              WHERE (id = ?)";
      $stmt = $pdo->prepare($query);
      $stmt->bindParam(1,$id, PDO::PARAM_INT);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
     logger()->critical('DB Error', (array) $e);
    }
    $description = $row['description'];
    $startdate = date('Y-m-d',strtotime($row['startdate']));
    $enddate = date('Y-m-d',strtotime($row['enddate']));
    $day[0] = $row['day_0'];
    $day[1] = $row['day_1'];
    $day[2] = $row['day_2'];
    $day[3] = $row['day_3'];
    $day[4] = $row['day_4'];
    $day[5] = $row['day_5'];
    $day[6] = $row['day_6'];
    $isRestricted = $row['isRestricted'];
    $UniqueCount = $row['UniqueCount'];
    $RequestsAllowed = $row['RequestsAllowed'];
    $starts = $row['Starts'];
    $ends = $row['Ends'];
    $AllowOverLimit = $row['AllowOverLimit'];
    $AffectLocks = $row['AffectLocks'];    
    $AffectsOthers = $row['AffectsOthers']; 
    $intSendEmails = $row['SendEmails'];     
  }
  else {
    $description = "";
    $startdate = date("Y-m-d");
    $nextyear = date("Y") + 1;
    $enddate = date($nextyear."-m-d");
    $day[0] = 1;
    $day[1] = 1;
    $day[2] = 1;
    $day[3] = 1;
    $day[4] = 1;
    $day[5] = 1;
    $day[6] = 1;
    $isRestricted = 0;
    $UniqueCount = 0;
    $RequestsAllowed = 4;
    $starts = 0;
    $AllowOverLimit = 1;
    $AffectLocks = 1;
    $AffectsOthers = 1;
    $ends = 366;  
    $minDate = 0;
    $maxDate = 0;
    $intHasRequests = 0;
    $intSendEmails = 1;  
  }
?>

<form id="leaveeditrequesttypeform">
  <table class="redtable" width="600px">
    <tr>
      <th><br>Request Type for <?php echo $strLeaveGroupDesc?><br><br></th>
    </tr>
    </table>  
    <div id="errorBox" class="lightcell">   
    </div>    
  <table class="redtable" width="600px">  
    <?php  
    if ($intHasRequests == 1) {
      echo '<tr>';
      echo '<td colspan="4">There are requsets of this type already in.<br>
            This means that the Start Date cannot be set later than '.$strMinDate.'<br>
            The End Date cannot be before '.$strMaxDate.'<br>';
      echo '</td>';
      echo '</tr>';      
    }     
    ?>  

    <tr>
      <td width="150px">Start Date</td>
      <td align="right"><input type="hidden" name="sDate" id="datepicker-start" value="<?php echo $startdate?>" required/></td>
      <td><input type="text" id="salternate" size="30" value="<?php echo date("l, j F, Y", strtotime($startdate))?>"></td>
    </tr>
    <tr>
      <td width="150px">End Date</td>
      <td align="right"><input type="hidden" name="eDate" id="datepicker-end" value="<?php echo $enddate?>" size="20" required/></td>
      <td><input type="text" id="ealternate" size="30" value="<?php echo date("l, j F, Y", strtotime($enddate))?>"></td>
    </tr>
    <tr>
      <td colspan="2">Description</td>
      <td><input type="text" name = "description" id="description" size="50" value="<?php echo $description?>"></td>
    </tr>
    </table>
  <table class="redtable" width="600px">
    <tr>
      <td colspan="4" align="center">Requests Allowed Per Day</td>
    </tr>
    <tr>
      <td width="25%">Saturday</td>
      <td><input type="text" name="0" size="10" value="<?php echo $day[0]?>"></td>
      <td width="25%">Sunday</td>
      <td><input type="text" name="1" size="10" value="<?php echo $day[1]?>"></td>
    </tr>
    <tr>
      <td width="25%">Monday</td>
      <td><input type="text" name="2" size="10" value="<?php echo $day[2]?>"></td>
      <td width="25%">Tuesday</td>
      <td><input type="text" name="3" size="10" value="<?php echo $day[3]?>"></td>
    </tr>
    <tr>
      <td width="25%">Wednesday</td>
      <td><input type="text" name="4" size="10" value="<?php echo $day[4]?>"></td>
      <td width="25%">Thursday</td>
      <td><input type="text" name="5" size="10" value="<?php echo $day[5]?>"></td>
    </tr>
    
    <tr>
      <td width="25%">Friday</td>
      <td><input type="text" name="6" size="10" value="<?php echo $day[6]?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td width="25%">Requests Start</td>
      <td><input type="text" name="starts" size="10" value="<?php echo $starts?>"> (Days)</td>
      <td width="25%">Requests End</td>
      <td><input type="text" name="ends" size="10" value="<?php echo $ends?>"> (Days)</td>      
    </tr>
    <tr>
      <td>Always Send eMails</td>
      <td colspan="3">
      <?php
      echo '<input type="checkbox" name="SendEmails" value="ON"';
        if ($intSendEmails == 1) {
          echo ' checked';
        }
        echo '>';
      ?>
      </td>
      </tr>

    <tr>
      <td>Restricted</td>
      <td>
      <?php
      echo '<input type="checkbox" name="isRestricted" value="ON"';
        if ($isRestricted == 1) {
          echo ' checked';
        }
        echo '>';
      ?>
      </td>
      <td>Allow Waiting List</td>
      <td>
      <?php
      echo '<input type="checkbox" name="AllowOverLimit" value="ON"';
        if ($AllowOverLimit == 1) {
          echo ' checked';
        }
        echo '>';
      ?>
      </td>      
      </tr>
      <tr>

      <td>Count Separately</td>
      <td>
      <?php
      echo '<input id="countsep" type="checkbox" name="UniqueCount" value="ON"';
        if ($UniqueCount == 1) {
          echo ' checked';
        }
        echo '>';
      ?>
      </td>
      <td class="depcheckedfalse" colspan="2"></td>
      <td class="depchecked">Number Allowed<br>Per Person Per Month</td>
      <td class="depchecked"><input type="text" name="NumberAllowed" size="10" value="<?php echo $RequestsAllowed?>"></td>
    </tr>
    
    <tr>
      <td>Affects Locks</td> 
      <td>
      <?php
      echo '<input id="affectlocks" type="checkbox" name="affectlocks" value="ON"';
        if ($AffectLocks == 1) {
          echo ' checked';
        }
        echo '>';
      ?>      
      </td>    
      <td>Affects Other Requests</td> 
      <td>
      <?php
      echo '<input id="AffectsOthers" type="checkbox" name="AffectsOthers" value="ON"';
        if ($AffectsOthers == 1) {
          echo ' checked';
        }
        echo '>';
      ?>      
      </td>     
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" value="Update" name="Update"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <input type="hidden" name="id" value="<?php echo $id?>">
  <input type="hidden" name="groupid" value="<?php echo $intGroupID?>">
</form>

<script type="text/javascript">
  
  $('document').ready(function() {
    function ltrimfun(str, chr) {
  var rgxtrim = (!chr) ? new RegExp('^\\s+') : new RegExp('^'+chr+'+');
  return str.replace(rgxtrim, '');
}
    $('#leaveeditrequesttypeform').validate({
      errorLabelContainer: "#errorBox",    
      rules:{
        "datepicker-start":{
          required:true,
          date: true,
        },
        "datepicker-end":{
          required:true,
          date: true,
        },
        "description":{
          required: {
        depends:function(){
            $(this).val(ltrimfun($(this).val()));
            return true;
        }
      },
      minlength: 1
          
        },
        "NumberAllowed":{
          required:true,
          min: -1,
          max: 999,
        },                
        "0":{
          required:true,
          min: -1,
          max: 999,
        },
        "1":{
          required:true,
          min: -1,
          max: 999,
        },        
        "2":{
          required:true,
          min: -1,
          max: 999,
        },        
        "3":{
          required:true,
          min: -1,
          max: 999,
        },        
        "4":{
          required:true,
          min: -1,
          max: 999,
        },
        "5":{
          required:true,
          min: -1,
          max: 999,
        },        
         "6":{
          required:true,
          min: -1,
          max: 999,
        },
        "starts":{
          required:true,
          min: 0,
        },
        "ends":{
          required:true,
          min: 0,
        },
      },
  messages: {
    description: {
      required:"Please Enter a Description for this Request Type<br>",
      minlength: jQuery.validator.format("Please, at least {0} characters are necessary")
      },
    NumberAllowed: "Please Enter a number requests per person per month allowed<br>",
    0: "Please Enter a number requests allowed for Saturday<br>",
    1: "Please Enter a number requests allowed for Sunday<br>",
    2: "Please Enter a number requests allowed for Monday<br>",
    3: "Please Enter a number requests allowed for Tuesday<br>",
    4: "Please Enter a number requests allowed for Wednesday<br>",            
    5: "Please Enter a number requests allowed for Thursday<br>",
    6: "Please Enter a number requests allowed for Friday<br>",     
    starts: "Please Enter the number of days before requests start<br>",    
    ends: "Please Enter the number of days when requests end<br>"   
  },   
  submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/admin/requesttypeedit.php', data:$('#leaveeditrequesttypeform').serialize(), success: function(data) {
        $.facebox.close();
        ShowRequestGroupConfig(<?php echo $intGroupID?>);           
      }});
  }   
  })
  });

  $(function() {
    $("#datepicker-start" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#salternate",
      altFormat: "DD, d MM, yy",
      <?php if ($minDate !=0) {
        echo 'maxDate: "'.$minDate.'"';
        }
      ?>
    });
  });
  $(function() {
    $( "#datepicker-end" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#ealternate",
      altFormat: "DD, d MM, yy",
      <?php if ($maxDate !=0) {
        echo 'minDate: "'.$maxDate.'"';
        }
      ?>
    });
  });

$('#countsep').change(function() {
  if($(this).prop("checked")) {
    $('.depchecked').show();
    $('.depcheckedfalse').hide();
  } else {
    $('.depchecked').hide();
    $('.depcheckedfalse').show();
  }
}).change();
</script>
<?php
 }
?>
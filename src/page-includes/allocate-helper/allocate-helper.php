<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
if ($arrUsersTeamdata["isSkillsAdmin"] == 1) {
  $date = date("Y-m-d");
  $intTeamID = $_POST['SchedulingteamId'] ?? '';
?>
  <table width="1000px">
    <tr>
      <td valign="top" colspan="2">
        <div class="tableheadersmall medtextboldcentre" id="dateinfo-<?php echo $intTeamID ?>" style="width: 100%"></div>
      </td>
    </tr>
    <tr>
      <td valign="top" width="200px">
        <div id="maindate-<?php echo $intTeamID ?>"></div>
        <div class="tableheadersmall"><br>Duties<br><br></div>
        <div id="dutiesac-<?php echo $intTeamID ?>" class="smalltext handcursor" style="overflow:auto; position:relative; height:300px"></div>
      </td>
      <td valign="top">
        <div id="alprog">
          <p>Please wait for this duty data.</p>
          <div id="myBarp"></div>
        </div>
        <div id="allochelper-<?php echo $intTeamID ?>" style="overflow:auto; position:relative; height:400px; width: 100%" class="smalltext">
          <div class="medtextboldcentre" id="skillsinfo">
            <br>Please choose a duty from the list on the left<br><br>
          </div>
        </div>
      </td>
    </tr>
  </table>
<?php
} else {
  echo 'Access Denied';
  die;
}
?>
<script language="javascript">
  $(function() {
    var teamID = $('.ui-accordion-header-active').attr('id');
    $("#maindate-" + teamID).datepicker({
      inline: true,
      showOtherMonths: 'true',
      selectOtherMonths: 'true',
      firstDay: '6',
      gotoCurrent: 'true',
      hideIfNoPrevNext: 'true',
      dateFormat: "yy-mm-dd",
      defaultDate: '<?php echo $date ?>',
      onSelect: function(dateText, inst) {
        var currentdate = dateText;
        ShowDateInfo(currentdate, teamID);
        ListDuties(teamID, currentdate);
      }
    });
  });

  function FillHelper(id, date, teamId) {
    $.post("page-includes/allocate-helper/fill-page.php", {
        teamId: teamId,
        id: id,
        date: date
      },
      function(data, status) {
        $('#allochelper-' + teamId).html(data);
      })
  }

  function ShowDateInfo(date, teamID) {
    $.post("page-includes/allocate-helper/date-info.php", {
        date: date
      },
      function(data, status) {
        $('#dateinfo-' + teamID).html(data);
      })
  }

  function ListDuties(teamID, date) {
    $.post("page-includes/allocate-helper/list-duties.php", {
        teamId: teamID,
        date: date
      },
      function(data, status) {
        $('#dutiesac-' + teamID).html(data);
      })
  }


  ShowDateInfo('<?php echo $date ?>', '<?php echo $intTeamID ?>');
  ListDuties('<?php echo $intTeamID ?>', '<?php echo $date ?>');
</script>
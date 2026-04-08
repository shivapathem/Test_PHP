<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';

$intDepartmentID = $_REQUEST['departmentid'];
$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrAccess = GetDepartmentAccessByLogin($strUser, $intDepartmentID);
$arrAccess["isAdmin"] = 1;
if ($arrAccess["isAdmin"] == 1) {
  echo '<div id="MDReportTabsDepts"style="overflow-x:scroll; width:100%">';
  echo '<ul>';
  echo '<li><a href="#MDReportTabs-0">Master Duties</a></li>';
  echo '<li><a href="#MDReportTabs-1">Manage Duties</a></li>';
  echo '<li><a href="#MDReportTabs-2">Manage Staff</a></li>';
  echo '</ul>';

  echo '  <div id="MDReportTabs-0" style="display:grid;">';
  echo '  </div>';
  echo '  <div id="MDReportTabs-1">';
  echo '  </div>';
  echo '  <div id="MDReportTabs-2">';
  echo '  </div>';
  echo '  </div>';
?>

  <script type="text/javascript">
    $(document).ready(function() {
      $(function() {
        $("#MDReportTabsDepts").tabs({
          'min-height': '400px',
          'overflow': 'auto',
          activate: function(event, ui) {
            var SelectedTab = $("#MDReportTabsDepts").tabs("option", "active");
            GetTabContent(SelectedTab);
          }
        });
      });
      ShowMDReport();
    })

    function GetTabContent(SelectedTab) {
      switch (SelectedTab) {
        case 0:
          ShowMDReport();
          break;
        case 1:
          ShowMDManage();
          break;
        case 2:
          ShowMDManageStaff();
          break;
      }
    }

    function ShowMDReport(weeknumber) {
      $.post("page-includes/reports/master-duties-team.php", {
          departmentid: $('#schedulingTeamSelect').val(),
          weeknumber: weeknumber
        },
        function(data, status) {
          $('#MDReportTabs-0').html(data);
        })
    }


    function ShowMDManage() {
      $.post("page-includes/reports/master-duties-manage.php", {
          departmentid: $('#schedulingTeamSelect').val()
        },
        function(data, status) {
          $('#MDReportTabs-1').html(data);
        })
    }

    function ShowMDManageStaff() {
      $.post("page-includes/reports/master-duties-teams.php", {
          departmentid: $('#schedulingTeamSelect').val(),
          admin: 1
        },
        function(data, status) {
          $('#MDReportTabs-2').html(data);
        })
    }
  </script>

<?php
} else {
  echo '<div id="MDReportTabs-0"></div>';

?>
  <script type="text/javascript">
    $(document).ready(function() {
      ShowMDReport();
    })

    function ShowMDReport(weeknumber) {
      $.post("page-includes/reports/master-duties-team.php", {
          departmentid: <?php echo $intDepartmentID ?>,
          weeknumber: weeknumber
        },
        function(data, status) {
          $('#MDReportTabs-0').html(data);
        })
    }
  </script>
<?php
}
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/testaccess.php';
include_once '../../class-includes/userRolePermissions.php';
include_once 'setTeamCookieRotas.php';

$intAreaID = $_SESSION['user']['AreaID'];
$intStaffID = $_SESSION['user']['StaffID'];
$pageid = 24; // Rotas Previous 6
// Call User Permission function.
$permissions = getUserRolePermissions($pageid);

if ($permissions->canview == 1) {
  echo '<div>';
  echo '<h1 id="rotaMainHeading" class="sr-only">View and edit rota patterns</h1>';
  echo '<div id="rotatabs" style="border:none;">';
    echo '<ul role="tablist">';
      echo '<li role="tab" aria-selected="true" onclick=\'javascript:ListRotaTabs(0,0); updateRotaMainHeading("View and edit rota patterns");\'><a href="#rotadiv0">View and Edit Rota Patterns</a></li>';
      echo '<li role="tab" aria-selected="false" onclick=\'javascript:ListRotaTabs(1,0); updateRotaMainHeading("People by Rota Pattern");\'><a href="#rotadiv0">People By Rota Pattern</a></li>';
      echo '<li role="tab" aria-selected="false" onclick=\'javascript:ListRotaTabs(2,0); updateRotaMainHeading("Rota Pattern by Person");\'><a href="#rotadiv0">Rota Pattern By Person</a></li>';
    echo '</ul>';

    echo '<div id="Loading6style" style="display:none"><img border="0" src="images/loading6.gif" width="145px" height="100px"></div>';

    echo '<div id="rotadiv0" style="width:100%;  max-height:100%;">';
    echo '</div>';
  echo '</div>';
}

?>

<script type="text/javascript">
$(document).ready(function(){
  if (<?php echo empty($permissions->canview) ? 0:$permissions->canview ?> == 0) {
    $( '#content' ).load( 'page-includes/no_access.php', function() { });
  }
  else {
      var tabCookieName = "rotatabs";
      var headings = ['View and edit rota patterns', 'People by Rota Pattern', 'Rota Pattern by Person'];

      $('#rotatabs > ul > li').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
          }
      });
      $("#rotatabs").tabs({
        active : ($.cookie(tabCookieName) || 0),
        activate : function( event, ui ) {
            var newIndex = ui.newTab.parent().children().index(ui.newTab);
            $.cookie(tabCookieName, newIndex, { expires: 1 });
            updateRotaMainHeading(headings[newIndex] || headings[0]);
            // Remove aria-labelledby to prevent duplicate announcements
            $('#rotadiv0').removeAttr('aria-labelledby');
        }
      });

    var selected = $("#rotatabs").tabs('option', 'active');

    // Update h1 heading based on initially selected tab
    updateRotaMainHeading(headings[selected] || headings[0]);

    // Remove aria-labelledby set by jQuery UI to prevent duplicate announcements
    $('#rotadiv0').removeAttr('aria-labelledby');

    ListRotaTabs(selected, 0);

  }
});

function updateRotaMainHeading(headingText) {
    $('#rotaMainHeading').text(headingText);
}

function updateRotaTabAriaSelected(selectedIndex) {
    $('#rotatabs > ul > li').each(function(index) {
        $(this).attr('aria-selected', index === selectedIndex ? 'true' : 'false');
    });
}

function ListRotaTabs(listtype, id) {
  switch (listtype) {
    case 0:
        $.ajax({
               type: 'POST',
                url: 'page-includes/rotas/rotaTab.php',
                data: {
                    "id": id
                },
                success: function (data) {
                    $('#rotadiv0').html(data);
                },
          });
      break;
      
      case 1:
        $.ajax({
          type: 'POST',
          url: 'page-includes/rotas/rotaPeopleTab.php',
          data: {
            "id": id,
            "tabCase": 1
          },
          success: function (data) {
            $('#rotadiv0').html(data);
            ListRotaPeople(0,0);
          },
        });
      break;

      case 2:
        $.ajax({
          type: 'POST',
          url: 'page-includes/rotas/rotaPeopleTab.php',
          data: {
            "id": id,
            "tabCase": 2
          },
          success: function (data) {
            $('#rotadiv0').html(data);
            ListRotaPeople(1,0)
          },
        });
      break;
  }
}
</script>
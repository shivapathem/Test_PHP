<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../users/process/classUserSetup.php';
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};
$setupObj = new classUserSetup();
$isSkillAdmin = $userRoleTrait->checkSkillAdminRole($_POST['teamid']);
if ($isSkillAdmin == 1) {
    if (isset($_SESSION['bst'])) {
        $bst = $_SESSION['bst'];
    } else {
        $bst = date("I");
        $_SESSION['bst'] = $bst;
    }
    $intTeamID = $_POST['teamid'];

    echo '<div class="tableheadersmall medtextboldcentre" width="550px">';
    echo '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
    echo '<tr>';
    echo '<td>Skills and Duties People can do</td>';
    echo '<td rowspan="2" align="right">';
    echo '<img border="0" src="images/clock.png" width="32" height="32" onclick="javascript:ToggleTimeZone(' . $intTeamID . ', 2);" class="handcursor" title="Clck here to change timezone"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>';
    echo 'You are Currently Viewing Duties in <font color="#990000">';
    if ($bst == 0) {
        echo "GMT";
    } else {
        echo "BST";
    }
    echo '</font>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';
    echo '<br>';

    echo '<table class="tablesmall" width="100%">';
    echo '<tr>';

    echo '<td valign="top" class="dutiesStaff">';
    echo '<div id="dutieslist_scd-' . $intTeamID . '">';
    echo '</div>';
    echo '</td>';

    echo '<td valign="top" class="dutiesStaff2ndcol">';
    echo '<div id="cando-' . $intTeamID . '">';
    echo '<div class="tableheadersmall medtextboldcentre" style="width:50%">';
    echo '<br>Choose a duty from the list<br><br>';
    echo '</div>';


    echo '</div>';
    echo '</td>';

    echo '</tr>';
    echo '</table>';
} else {
    echo 'Access Denied'; die;
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        FillDutiesList(0, <?php echo $intTeamID?>);
    })

    function FillDutiesList(id, teamid) {
        $.post("page-includes/teamskills/view/skills-fill-duties-list.php", {
                id: id,
                teamid: teamid,
                page: 1,
                showbuttons: 0
            },
            function (data, status) {
                $('#dutieslist_scd-<?php echo $intTeamID?>').html(data);
            }
        )
    }

    function FillCanDoPage(id, teamid) {
        $.post("page-includes/teamskills/view/skills-duties-cando-fillpage.php", {
                id: id
            },
            function (data, status) {
                $('#cando-<?php echo $intTeamID?>').html(data);
            }
        )
    }

    function EditDuty(id) {
        $('#popup').bPopup({
            easing: 'easeOutBack',
            speed: 450,
            transition: 'slideDown',
            contentContainer: '#popcontent',
            loadUrl: 'page-includes/ajax-calls/skills-newedit-duty.php?id=' + id + '',
        })
    }

    function DutyDays(id) {
        $('#popup').bPopup({
            easing: 'easeOutBack',
            speed: 450,
            transition: 'slideDown',
            contentContainer: '#popcontent',
            loadUrl: 'page-includes/ajax-calls/skills-duty-days.php?id=' + id + '',
        })
    }

    function DeleteDuty(id) {
		customConfirm('Do you want to delete this Duty?<br/>It will remove all programmes associated with it!',function(){
				$.post("page-includes/ajax-calls/skills-delete-duty.php", {
						id: id
					},
					function (data, status) {
						FillDutiesList(0);
						FillDutiesPage(0);
					}
				)
			},
			function() {
				return false;
			}
		);
    }

    $('#programmes td').click(function (e) {
        $('#programmes td').removeClass('highlighted');
        $(this).addClass('highlighted');

    });

</script>
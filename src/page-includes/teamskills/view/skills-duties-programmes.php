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
    echo '<td>Assign Skills to Duties</td>';
    echo '<td rowspan="2" align="right">';
    echo '<img border="0" src="images/clock.png" width="32" height="32" onclick="javascript:ToggleTimeZone(' . $intTeamID . ', 1);" class="handcursor" title="Clck here to change timezone"></td>';
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
    echo '<div id="dutieslist-' . $intTeamID . '">';
    echo '</div>';
    echo '</td>';

    echo '<td valign="top" class="dutiesStaff2ndcol">';
    echo '<div id="dutiesprogrammes-' . $intTeamID . '">';
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
                teamid: teamid
            },
            function (data, status) {
                $('#dutieslist-<?php echo $intTeamID?>').html(data);
            }
        )
    }

    function FillDutiesPage(id, teamid) {
        $.post("page-includes/teamskills/view/skills-duties-progs-fillpage.php", {
                id: id,
                teamid: teamid
            },
            function (data, status) {
                $('#dutiesprogrammes-<?php echo $intTeamID?>').html(data);
            }
        )
    }


    function EditDuty(id, teamid) {
        $.post("page-includes/teamskills/view/skills-new-edit-duty.php", {
                id: id,
                teamid: teamid
            },
            function (data, status) {
                $.facebox(data);
            })
    }

    function ToggleDutyDay(id, day) {
        $.post("page-includes/teamskills/view/skills-toggle-day.php", {
                id: id,
                day: day
            },
            function (data, status) {
                FillDutiesList(id, <?php echo $intTeamID?>);
                FillDutiesPage(id, <?php echo $intTeamID?>);
            })
    }

    function ToggleGMTBST(id, gmtbst) {
        $.post("page-includes/teamskills/view/skills-toggle-gmtbst.php", {
                id: id,
                gmtbst: gmtbst
            },
            function (data, status) {
                FillDutiesList(id, <?php echo $intTeamID?>);
                FillDutiesPage(id, <?php echo $intTeamID?>);
            })
    }

    function AddProgToDuty(progid, dutyid) {
        $.post("page-includes/teamskills/view/skills-add-prog-to-duty.php", {
                dutyid: dutyid,
                progid: progid
            },
            function (data, status) {
                FillDutiesPage(dutyid, <?php echo $intTeamID?>);
            }
        )
    }

    function RemoveProgFromDuty(progid, dutyid) {
        $.post("page-includes/teamskills/view/skills-remove-prog-from-duty.php", {
                dutyid: dutyid,
                progid: progid
            },
            function (data, status) {
                FillDutiesPage(dutyid, <?php echo $intTeamID ?>);
            }
        )
    }


    function DeleteDuty(id){
		customConfirm('Do you want to delete this Duty?<br/>It will remove all programmes associated with it!',function(){
				$.post("page-includes/teamskills/view/skills-delete-duty.php", {
                    id: id
                },
                function (data, status) {
                    $.facebox.close();
                    FillDutiesList(0, <?php echo $intTeamID?>);
                    FillDutiesPage(0, <?php echo $intTeamID?>);
                })
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
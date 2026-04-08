<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../../function-includes/init.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../users/process/classUserSetup.php';
$setupObj = new classUserSetup();
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
if (($intSysAdmin == 1) || !empty($userDivisionsList)) {
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrColours = GetShiftTextColours ();
$divisionMap = [];
if(!empty($userDivisionsList)){
    foreach($userDivisionsList as $division){
        $divisionMap[$division['DivisionID']] = $division['DivisionName'];
    }
}
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
echo '<br><h2 class="sr-only">Text Colours</h2>The text Colour for the Weekly/Multi Week and Monthly Views is defined here.<br>These settings can only be used for days which are not working days.<br>This will match the start of the duty name.<br>\'Leave\' and \'Sick\' will need to be added to these entries.<br><br>';
echo '<div class="DutyCellBottomLeft" onclick="javascript:EditStaffColour(0)";>';
echo '<table>';
echo '<tr>';
echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
echo '<td>';
echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '</div> ';
echo '<table class="tablesmall compact stripe" id="offlist">';
echo '<thead>';
echo '<tr>';
echo '<th>';
echo 'Description';
echo '</th>';
echo '<th>';
echo 'Area';
echo '</th>';
echo '<th>';
echo 'Text Colour';
echo '</th>';
echo '<th>';
echo '</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
if (isset($arrColours)) {
    foreach ($arrColours as $intID => $arrColour ) {
        $colour = is_null($arrColour['TextColour']) ? '000000' : $arrColour['TextColour'];
        $description = $arrColour['Description'];
        $divisionID = $arrColour['DivisionID'];
        echo '<tr id="tr'.$intID.'">';
        echo '<td class="handcursor" ondblclick="javascript:EditStaffColour('.$intID.', \'',$colour.'\', \''.$description.'\', \''.$divisionID.'\')";>';
        echo $arrColour['Description'];
        echo '</td>';
        echo '<td class="handcursor" ondblclick="javascript:EditStaffColour('.$intID.', \'',$colour.'\', \''.$description.'\', \''.$divisionID.'\')";>';
        echo isset($divisionMap[$divisionID]) ? $divisionMap[$divisionID] : '';
        echo '</td>';
        // Colour picker
        echo '<td class="handcursor">';
        $colourDefault = "'".$colour."'";
        $newIntId = "'#".$intID."'";
        echo '<input type="text" value="" id="'.$intID.'" class="handcursor" onchange="savecolour('.$intID.', this.value);"></input>';
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                colorPickerDropdown(<?php echo $newIntId; ?>, <?php echo $colourDefault; ?>);
            });
        </script>
        <?php
        echo '</td>';
        echo '<td class="handcursor" onclick="javascript:DeletetStaffColour('.$intID.')";>';
        echo '<img border="0" src="images/delete.gif" width="16" height="16">';
        echo '</td>';
        echo '</tr>';
    }
}
echo '</tbody>';
echo '</table>';
 } else {
    echo 'Access Denied'; die;
   }
?>

<div id="dialog-staffcolour-delete" title="Question!" style="display:none;">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this entry?</span></p>
</div>

<script type="text/javascript">
    $(document).ready( function () {

        var offlist = $("#offlist").DataTable({
            paging: false,
            destroy: true,
            scrollY: 500,
            info:     false,
            stateSave: true,
            deferRender: true,
        });
        yadcf.init(offlist, [
            {column_number: 0,
                filter_type: 'text'
            },
        ]);

        if ( $.cookie("offlist") !== null ) {
            scrollPos = $.cookie("offlist");
            $('#offlist').closest('.dataTables_scrollBody').scrollTop(scrollPos);
        }
    })
    $('#offlist').closest('.dataTables_scrollBody').on('scroll', function() {
        var currpos = $('#offlist').closest('.dataTables_scrollBody').scrollTop();
        $.cookie("offlist", currpos);
    });

    function savecolour(id, colour) {
        $.post("page-includes/admin/system-admin-staff-colours-update-colour.php", {
                id: id,
                colour: colour
            },
            function(data,status){
                ShowSystemAdminColours();
            }
        )
    }

    function DeletetStaffColour(id) {
        $( "#dialog-staffcolour-delete" ).dialog(
            {
                width:400,
                buttons: {
                    "Yes": function() {
                        $( this ).dialog( "close" );
                        $.post("page-includes/admin/system-admin-staff-colours-delete.php", {
                            id: id
                        })
                        ShowSystemAdminColours();
                    },
                    "No": function() {
                        $( this ).dialog( "close" );
                    },
                }
            }
        );
    }

    function EditStaffColour (id, colour, description, divisionID) {
        $.post("page-includes/admin/system-admin-staff-colours-edit.php", {
                id: id,
                colour: colour,
                description:description,
                divisionID:divisionID
            },
            function(data,status){
                $.facebox(data);
            })
    }
</script>
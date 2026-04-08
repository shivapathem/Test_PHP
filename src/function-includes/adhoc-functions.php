<?php
//get scheduling team
function TeamListDropDown($rsAreaTeams, $selectedTeam)
{
    $team_list = null;
    $rsTeams = json_decode($rsAreaTeams, true);
    $team_list .= '';
    $arrCount = count($rsTeams);
    $team_list .= "<option value=''>Select The Team</option>";
    for ($row = 0; $row < $arrCount; $row++) {
        $selectvalue = $selectedTeam == $rsTeams[$row]['TeamID'] ? "selected" : "";
        $team_list .= '<option value="' . $rsTeams[$row]['TeamID'] . '"' . $selectvalue . '>' . $rsTeams[$row]['TeamName'] . '</option>';
    }
    if (isset($team_list)) {
        return ($team_list);
    }
} 

?>
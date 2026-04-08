<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../../function-includes/DB_Functions.php';
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/userRolePermissions.php';


class xmasPointUI
{
    
  function teamXmaspointHtmlCall($scheduledTeamList,$hasProductionGroupAccess,$isSysAdmin){
    $pageid = 14;
    // Call User Permission function.
    $activeclass = '';
    $permissions = getUserRolePermissions($pageid);
    //set the permissions
    if ($permissions->cancreate == 0 ) {
        $activeclass = 'activeclass';
    }
    if ($permissions->candelete == 0 ) {
      $activeclass = 'activeclass';
  }
      
    $xmasLists ='';
    $xmasLists .='<script src="../../../js/xmasPointsOfTeam.js"></script>
		
	<div id="adminuserstabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
				<ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
					
				<li role="tab" tabindex="0" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover ui-state-focus" aria-controls="scheduling" aria-labelledby="ui-id-1" aria-selected="true" aria-expanded="true" onclick="javascript:showSchedulingTeamUI()";><a href="#scheduling" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-1">Scheduling Team</a></li>';
        if($hasProductionGroupAccess){
          $xmasLists .='<li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="prodgroups" aria-labelledby="ui-id-4" aria-selected="false" aria-expanded="false" onclick="javascript:ShowProductionViewGroupFilters(0)";><a href="#prodviewgroups" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-2">Production View Groups</a></li>';
        }
        $xmasLists .=	'<li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover ui-state-focus ui-tabs-active ui-state-active" aria-controls="christmas" aria-labelledby="ui-id-1" aria-selected="false" aria-expanded="false" onclick="javascript:showTeamXmasPoint()";><a href="#christmas" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-3">Christmas Points</a></li>
				
				        <li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover ui-state-focus" aria-controls="extra" aria-labelledby="ui-id-3" aria-selected="false" aria-expanded="false" onclick="javascript:showSchedulingTeamExtraXmasPointUI()";><a href="#extra" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-4">Extra Christmas Points</a></li>';
        
        
				$xmasLists .='</ul>
	</div>

     <input type="hidden"  name = "js_sysadmin" value="'.$isSysAdmin  .'" id="js_sysadmin"> 
                  <div id="searchScheduleperson">

                            <div class="tableheadersmall medtextboldcentr " style="position: relative; width:100%">
                               <div class="main-heading second-heading"><br><b>Double-Click on the Date to set Basic Points.</b><br>';
                               if($permissions->cancreate) { 
                                $xmasLists .= '<b>Double-Click on the Start Time to edit a Time Window.</b><br>';
                               }
                               if($permissions->cancreate) { 
                                $xmasLists .= '<b>Click on the icon to add a Time Window.</b><br><br>';
                               }
                               $xmasLists .= '</div></div>
                                <div class="fieldsection m-1">
                                    <div class="parant_div_1" id="parant_div_1">
                                        <div class="child_div_1 no-border additonaltemtd ">
                                                <div class="fields m-tb">
                                                    <label  for="hometeam" id="w-0"> Scheduling Team</label>
                                                    <select name="hometeam" class= "teams-seclect" id="js_teamdropdownxmas">
                                                        <option data-divisionid="0" value="">Select The Team</option>';
                                                      foreach ($scheduledTeamList as $key => $team) {
														  if($team['hasXmasPoints'])
														  {
                                                            $xmasLists.='<option data-divisionid ="'.$team['DivisionId'].'" value="'. $team['TeamID'].'">'.$team['TeamName'].'</option>';
														  }
                                                        } 
                                                        $xmasLists.='</select>
                                                </div>
                                        </div>
                                    </div>
                            </div>
  
                            <div class="tables">
                                <div class="">
                                      <table class="tablesmallgrey" id="xmaspoints" min-width="100%">
                                      <thead>
                                          <tr>
                                                <th width="200px"> Date</th>
                                                <th width="200px">Basic Points</th>
                                                <th width="200px">Limit</th>
                                                <th width="200px">Start Time</th>
                                                <th width="200px">End Time </th>
                                                <th width="200px">Points </th>
                                                <th width="70px">Delete </th>
                                                <th width="75px">Add</th>
                                            </tr>
                                      </thead>
                                    <tbody>
                                    <tr><td class="no-record p-11" colspan=8>Please Apply filter to display records.</td></tr>
                                    </tbody>
                                    </table>
                              </div>
                            </div>
                          </div>';
                     
    return $xmasLists;
  }

  function xmasTableListingDesign($arrPoints,$teamid){
    $tabledesign = '';
      $arrDates = array('23','24', '25', '26', '27', '28', '29', '30', '31', '1');
     
      foreach ($arrDates as $intArrID => $intDOTW) {
        if (isset($arrPoints[$intDOTW]['Times'])) {
          $intRowCount = count($arrPoints[$intDOTW]['Times']);
        }
        else {
          $intRowCount = 1;
        }
       
        $tabledesign.='<tr>
                        <td rowspan="'.$intRowCount.'" class="handcursor" id ="js_editxmaspointsday"  data-xmasday="'.$intDOTW.'" data-teamid="'.$teamid.'" >';
                        $tabledesign.=  $intDOTW;
        $tabledesign.='</td>
        <td rowspan="'.$intRowCount.'">';
        if (isset($arrPoints[$intDOTW])) {
          $tabledesign.= $arrPoints[$intDOTW]['Basic'];
        }
        $tabledesign.='</td>
        <td rowspan="'.$intRowCount.'">';
        if (isset($arrPoints[$intDOTW])) {
          $tabledesign.= $arrPoints[$intDOTW]['Limit'];
        }
        $tabledesign.='</td>';
        if (!isset($arrPoints[$intDOTW]['Times'])) {
          $tabledesign.='<td></td>
                      <td></td>
                      <td></td>
                      <td></td>';
        }
        else {
          $intFirstArray = key($arrPoints[$intDOTW]['Times']);
          $tabledesign.='<td class="handcursor" id="js_editxmaspointxub" data-subxmaspointid="'.$intFirstArray.'" data-xmasday="'.$intDOTW.'" data-teamid="'.$teamid.'">';
          $tabledesign.=  gmdate("H:i", $arrPoints[$intDOTW]['Times'][$intFirstArray]['StartTime']);
          if($arrPoints[$intDOTW]['Times'][$intFirstArray]['StartTime'] >= 86400) {
            $tabledesign.='*';
          }    
          $tabledesign.=' </td>
          <td>';
          $tabledesign.= gmdate("H:i", $arrPoints[$intDOTW]['Times'][$intFirstArray]['EndTime']);
          $tabledesign.='</td>
          <td>';
          $tabledesign.=  $arrPoints[$intDOTW]['Times'][$intFirstArray]['Points'];
          $tabledesign.='</td>  
          <td align="center" class="handcursor" id="js_deletexmaspointsub" data-subxmaspointid="'.$intFirstArray.'" ;>
          <i class="fa fa-trash anchor-colour iconstyleallocate7"> </i>         
          </td>'; 

        }
        $tabledesign.='<td align="center" rowspan="'.$intRowCount.'">';
        if (isset($arrPoints[$intDOTW])) {  
          
          $tabledesign.='<i class="fa fa-plus anchor-colour iconstyleallocate7" Style="cursor: pointer" id="js_editxmaspointxub" data-subxmaspointid="0" data-xmasday="'.$intDOTW.'" data-teamid="'.$teamid.'" ></i>';
        }
        $tabledesign.='</td>
        </tr>';
        if (isset($arrPoints[$intDOTW]['Times'])) {
          foreach ($arrPoints[$intDOTW]['Times'] as $intRecID => $arrTimes) {
            if ($intRecID != $intFirstArray) {
              $tabledesign.='<tr>
              <td class="handcursor" id="js_editxmaspointxub" data-subxmaspointid="'.$intRecID.'" data-xmasday="'.$intDOTW.'" data-teamid="'.$teamid.'" >';
              $tabledesign.= gmdate("H:i", $arrTimes['StartTime']);
              if($arrTimes['StartTime'] >= 86400) {
                $tabledesign.='*';
              }
              $tabledesign.='</td>
              <td>';
              $tabledesign.=  gmdate("H:i", $arrTimes['EndTime']);
              $tabledesign.='</td>
              <td>';
              $tabledesign.=$arrTimes['Points'];
              $tabledesign.='</td>
              <td align="center" class="handcursor" id="js_deletexmaspointsub" data-subxmaspointid="'.$intRecID.'";>
              <i class="fa fa-trash  anchor-colour iconstyleallocate7"></i>        
              </td>       
              </tr>';
            } 
          }
        
        }
        $tabledesign.='<tr>
          <th height="2px" colspan= "8"
          </th>
          </tr>';
      }
      return $tabledesign;
  }

  function xmaspoinByDayPopup($params){
   
    $xmaspointday = '';
    $xmaspointday .='<form id="xmasform">
            <table class="redtable" width="600px">
            <tr height="30px">
            <th colspan="2">Editing Christmas Points</th>
            </tr>
            <th width="100px">Basic Points</th>
            <td><input type="text" name="basicpoints" size="10" value="'.$params['BasicPoints'].'"></td>
            </tr>
            <tr>
            <th width="100px">Maximum Points</th>
            <td><input type="text" name="limit" size="10" value="'.$params['Limit'].'"></td>
            </tr>

            <tr>
            <td>&nbsp;</td>
            <td><input type="submit" value="Update" name="Update" id="js_addxtrapointubmit"></td>
            </tr>
            </table>
            <input type="hidden" name="teamid" value="'.$params['teamid'].'">
            <input type="hidden" name="day" value="'.$params['day'].'">
            <input type="hidden" name="xmaspointid" value="'.$params['xmasPointsByTeamID'].'">
            <input type="hidden" name="action" value="xmasdaypoint">
            </form>';

        return $xmaspointday;
  }



function xmasPointByTimePopup($param){
  $intStartTime = $param['subxmaspointid'] == 0 ? '00:00' : gmdate('H:i', $param['xmaspointBySubDetails']['StartTime']);
  $intEndTime = $param['subxmaspointid'] == 0 ? '00:00' : gmdate('H:i', $param['xmaspointBySubDetails']['EndTime']);
  $intPoints = $param['subxmaspointid'] == 0 ? 0 : $param['xmaspointBySubDetails']['Points'];
  $intAfterMidnight = $param['subxmaspointid'] == 0 ? 0 : ($param['xmaspointBySubDetails']['StartTime'] >= 86400 ? 1 : 0);
  // $intAfterMidnight = $intStartTime>= 86400 ? 1:0;
  $xmaspointsub = '';
  $xmaspointsub .='<form id="xmasformsub">
        <table class="redtable" width="600px">
        <tr height="30px">
        <th colspan="2">Editing Christmas Points</th>
        </tr>

        <th width="100px">Start Time</th>
        <td>
        <input id="starttime" name="starttime" type="text" class="ui-timepicker-input" size="10" onChange="jobsTimeSanitizationXmasPoint(starttime.value,0);" autocomplete="off" value="' . htmlspecialchars($intStartTime) . '"/>
        </td>
        </tr>

        <th width="100px">End Time</th>
        <td>
        <input id="endtime" name="endtime" type="text" class="ui-timepicker-input" size="10" onChange="jobsTimeSanitizationXmasPoint(endtime.value,1);" autocomplete="off" value="' . htmlspecialchars($intEndTime) . '"/>
        </td>
        </tr>

        <tr>
        <th width="100px">Points</th>
        <td><input type="text" name="points" size="10" value="' . htmlspecialchars($intPoints) . '"></td>
        </tr>

        <tr>
        <th width="100px">After Midnight</th>
        <td>
          <input name="AfterMidnight" id="AfterMidnight" value="ON" type="checkbox"';
          if ($intAfterMidnight === 1) {
            $xmaspointsub .=' checked';
          }
          $xmaspointsub .= '> 

        </td>
        </tr>

        <tr>
        <td>&nbsp;</td>
        <td><input type="submit" value="Update" name="Update" id="js_addxmassubpoint"></td>
        </tr>
        </table>
        <input type="hidden" name="teamid" value="' . htmlspecialchars($param['teamid']) . '">
        <input type="hidden" name="day" value="' . htmlspecialchars($param['day']) . '">
        <input type="hidden" name="subxmaspointid" value="' . htmlspecialchars($param['subxmaspointid']) . '">
        <input type="hidden" name="action" value="xmassubpoint">
        </form>';
       $xmaspointsub .="
	   <script>
	   $(function () {
			$('#starttime').timepicker({
				'step': 15,
				'timeFormat': 'H:i'
			});
		});

		$(function () {
			$('#endtime').timepicker({
				'step': 15,
				'timeFormat': 'H:i'
			});
		});

  function jobsTimeSanitizationXmasPoint(timeVal,elementtype)
  {
   
    let elementResorce = '';
      if(elementtype == 0){
        elementResorce	=	document.getElementById('starttime');
      }else{
        elementResorce	=	document.getElementById('endtime');
      }
      if(timeVal == '0')
      {
          setTimeout(function(){
              elementResorce.value = '00:00';
              elementResorce.blur();
          }, 1000);
          return true;
      }
      let timeValArr	=	timeVal.split(':');
      if(timeValArr[0] < 24 && timeValArr[1] < 60)
      {
      }else
      {
          elementResorce.value	=	'00:00';
      }
  }
		</script>";
        return $xmaspointsub;
  }
}


<?php

//Allocater Users HTML for different functions
class classAllocateUsersUI
{
    /*
    * @Description :Inital Design for New Allocate users.
	* @access : Public
	* @global : Not Applicable
	* @param  : no param
	* @return : HTML
    */
    function getAllocateUsersHTML($param)
    {
        $Title = $param['title'] == "Non Scheduled Team" ? "Add New Non Scheduled Staff Member" : "Add New ".$param['title']." User";
        $addview = '';
        $addview .= '<form id="'.$param['formname'].'">
                    <table class="redtable" width="600px" id ="js_usersAD">
                    <tbody>
                    <tr>
                    <th colspan="2" align="center"><br>'.$Title.'<br><br></th>
                    </tr>
                    <tr>
                    <tr id ="bugtd">
                    <td colspan="2" class ="messageerror error" align="center" ></td>
                    </tr>
                    <tr>
                    <td>Network Login</td>
                    <td>
                    <input type="text" style="width:250px" id="js_netLogin" name="netLogin" size="35" value="">
                    </td>
                    </tr>
                    </tr>
                    <td></td>
                    <td><input id = "'.$param['buttonid'].'" name="submit" type="submit" value="Add User"></input></td>
                    </tr>
                    </tbody>
                    </table>
                <input type="hidden" name="'.$param['hiddenactionname'].'" value="'.$param['hiddenactionvalue'].'">
                <input type="hidden" name="teamid" value="'.$param['teamid'].'">
            </form>';
        return $addview;
    }

    /*
    * @Description :Check user exist or not design
	* @access : Public
	* @global : Not Applicable
	* @param  : no param
	* @return : HTML
    */
    function getCheckUserHTML($errormessage)
    {
        $addview = '';
        $addview .= ' <table class="redtable" width="600px" id ="js_usersAD">
                    <tbody>
                    <tr>
                    <th colspan="2" align="center"><br>New Allocate User<br><br></th>
                    </tr>
                    <tr>
                    <tr>
                    <td colspan="2" class ="messageerror error" align="center"><br>' . $errormessage['strreturnstring'] . '<br><br></td>
                    </tr>
                    <tr>
                    <td>' . $errormessage['strreturnstring2'] . '</td>
                    <td>' . $errormessage['strreturnstring3'] . '</td>
                    </tr>';
                    if($errormessage['type']=='nonscheduled'){
                        $addview .= '   <td>' . $errormessage['strreturnstring4'] . '</td>
                    <td>' . $errormessage['strreturnstring5'] . '</td>';
                    }
                    $addview .= '</tr>
                    </tbody>
                    </table>
                ';

        return $addview;
    }

    /*
        * @Description :Display User Info
        * @access : Public
        * @global : Not Applicable
        * @param  : no param
        * @return : HTML
    */

    function userInfoHTML($roleLists,$userStaffDetails,$getallocateUserInfoResult,$arrUserSettings){
	    $userInfoView ='';
        $userInfoView .= '<div class="p-0" style="width:1200px!important;">
                            <div class="fieldsection">
                            <div class="parant_div_1 fieldmargin" id="parant_div_1">
                                <h6>Information for '.$userStaffDetails['PreferredForename'].' ('.$userStaffDetails['NetLogin'].')<br></h6>
                            </div>
                        </div>

                        <section class="section-margin">
                            <div class="tables access-table">
                                <div class="scrollable maxheight" style="overflow:scroll;" >
                                    <table class="oddevenclass tablesmall stripe  dataTable no-footer" id="joblisting"
                                       role="grid" aria-describedby="joblisting_info">
                                        <thead>
                                        <th class= "min200">Scheduling Team </th>
                                        <th>Default </th>';
                                        foreach($roleLists as $role){
                                                $userInfoView .= '<th> '.$role['RoleName'].' </th>';
                                            }

                                        $userInfoView.='</thead>
                                        <tbody class="context-menu-one">';
                                            if(!empty($getallocateUserInfoResult)){
                                                foreach($getallocateUserInfoResult as $userdata){
                                                    $userInfoView .= '<tr> ' ;
                                                    $userInfoView .= '<td> '.$userdata['schedulingTeamName'].' </td>';
                                                    $userInfoView .= '<td> '.$userdata['isDefault'].' </td>';
                                                    foreach($roleLists as $role){
                                                        $userInfoView .= '<td> '.$userdata[$role['RoleName']].' </td>';
                                                    }
                                                    $userInfoView.= '</tr>';
                                                }
                                            } else{
                                                $colspane = count($roleLists) + 4;
                                                $userInfoView .= '<tr> ' ;
                                                    $userInfoView .= '<td colspan="'.$colspane.'" class="filter_data_unavailabl">No Record Found</td>
                                                ';
                                                    $userInfoView.= '</tr>';
                                                }
                                        $userInfoView .= '</tbody>
                                        </table>
                                </div>
                            </div>
                        </section>';
                        if(!empty($arrUserSettings)){
                        $userInfoView.=' <section>
                        <div class="tables access-table sec-acces-table sec-width">
                            <div>
                                <table class="oddevenclass tablesmall stripe  dataTable no-footer" id="joblisting"
                                       style="width: 100%;" role="grid" aria-describedby="joblisting_info">
                                    <thead class="headerSticky">
                                    <th>Leave & Request Group(s)</th>
                                    <th>Administrator</th>
                                    </thead>
                                    <tbody class="context-menu-one">';
                                    if(!empty($arrUserSettings['LeaveRequests'])){
                                     foreach ($arrUserSettings['LeaveRequests'] as $intGroupID => $arrLeaveRequest)  {
                                        $userInfoView.='<tr><td> '.$arrLeaveRequest['Description'].'</td>';

                                            switch ($arrLeaveRequest['Admin']) {
                                                case 3:
                                                   $adminimg= '<img title="You may Leave manage the configuration of this Group" border="0" src="../images/purple_tick.png" alt="Leave Manager" width="12px" height="12px">';
                                                   break;
                                                case 2:
                                                $adminimg= '<img title="You may administer the configuration of this Group" border="0" src="../images/green_tick.png" alt="Administrator" width="12px" height="12px">';
                                                break;
                                                case 1:
                                                $adminimg= '<img title="You may approve Leave in this Group" border="0" src="../images/yellow_tick.png" alt="Authoriser" width="12px" height="12px">';
                                                break;
                                                default:
                                                $adminimg= '<img title="You may request Leave in this Group" border="0" src="../images/red_cross.png" alt="Requester" width="12px" height="12px">';
                                                break;
                                            }

                                            $userInfoView.='  <td class="center-text">'.$adminimg.' </td>
                                            </tr>';
                                     }
                                    }
                                     $userInfoView.='  </tbody>
                                </table>
                            </div>
                        </div>
                    </section>';
                    }
                $userInfoView.='</div>';

       return $userInfoView;
    }
}

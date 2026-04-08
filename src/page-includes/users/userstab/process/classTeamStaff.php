<?php
include_once __DIR__ . '/../../../../function-includes/DBHelper.php';
include_once __DIR__ . '/../../../../function-includes/DB_Functions.php';
include_once __DIR__ . '/../../../../function-includes/testaccess.php';
include_once __DIR__ . '/../../../../class-includes/userRolePermissions.php';

class classTeamStaff
{

public $userID = '';
public $userName = '';

public function __construct()
{

    $this->userID =isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
    $this->userName = isset($_SESSION['user']['DisplayName']) ? $_SESSION['user']['DisplayName'] : (isset($_SESSION['user']['PreferredForename']) ? $_SESSION['user']['PreferredForename'] : $_SESSION['user']['FullName']);
    
}
/*
* @Description : set staff permissions
* @access : Public
* @global : Not Applicable
* @param  : $searchUser,$searchTeam,$actionType=''
* @return : Return the assigned duties filter resulrt
*/
function setUsersPermissions($teamid, $userid,$roleid,$actionvalue,$action,$schedulepersonid,$moduleName='')
{
    $moduleName = 'NonScheduledTeamStaff';
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_mod_setUsersPermissions] ?,?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $userid, PDO::PARAM_INT);
    $stmt->bindParam(2, $teamid, PDO::PARAM_INT);
    $stmt->bindParam(3, $roleid, PDO::PARAM_INT);
    $stmt->bindParam(4, $action, PDO::PARAM_STR);
    $stmt->bindParam(5, $this->userID, PDO::PARAM_INT);
    $stmt->bindParam(6, $actionvalue, PDO::PARAM_INT);
    $stmt->bindParam(7, $moduleName, PDO::PARAM_STR);
    $stmt->bindParam(8, $this->userName , PDO::PARAM_STR);
    $stmt->bindParam(9, $schedulepersonid , PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$jsonresult = json_encode($result);
    return $jsonresult;
}

    /*
* @Description : set staff permissions
* @access : Public
* @global : Not Applicable
* @param  : $searchUser,$searchTeam,$actionType=''
* @return : Return the assigned duties filter resulrt
*/
function getSearchTeamStaff($teamid, $usertype,$divisionid)
{
    if($teamid) {
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_get_ScheduledNonScheduled] ?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $teamid, PDO::PARAM_STR);
        $stmt->bindParam(2, $usertype, PDO::PARAM_STR);
        $stmt->execute();
        $resultSearchStaff = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($resultSearchStaff)) {
            $resultdata[] = array('', '', '', '', '', '<p class="no-record-filter">No data for selecting team</p>', '', '', '', '', '', '', '', '');
        }else{
            $resultdata = $this->dataFormatDatatable($resultSearchStaff,$usertype,$divisionid);
        }
    } else {
        $resultdata[] = array('', '', '', '', '', '<p class="no-record-filter">Please apply the filter</p>', '', '', '', '', '', '', '', '');
    }
    
    $jsonresult = json_encode($resultdata);
    return $jsonresult;

}
/*
* @Description : Response result to getUserSetupByIdNetlogin functiomn in json for datatble array .
* @access : Public
* @global : Not Applicable
* @param  : N/A
* @return : Array output
*/

private function dataFormatDatatable($resultSearchStaff,$usertype,$divisionid){

    $pageid = 11;
    // Call User Permission function.
    $activeclass = $disabled= $delsactiveclass='';
    $roletype = 1;
    $getAdditionalRoleLists = json_decode(getRoleLists($roleaction = '',$roletype),true);
    $mainroletype = 0;
    $counterkey = 0;
    $selectedvalue = 0;
    $dropdwonclass = '';
    $sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

    if($usertype == 0){
        $getMainRoleLists = json_decode(getRoleLists($roleaction = '',$mainroletype),true);
    } 

    $current_netlogin = $sessUserNetLogin;

    $schedulingTeamId  = !empty($resultSearchStaff)? $resultSearchStaff[0]['TeamID'] :0;
    $hideSchedulingTeamOption = 0;
    if ($_SESSION['user']['SysAdmin'] != 1 && $divisionid == 0) {
        $permissions = getUserRoleByTeam($pageid,$schedulingTeamId);
       
        //set the permissions
        if ($permissions->canmodify == 0 ) {
            $activeclass = 'activeclass';
            $disabled = 'notclickable';
        }
        if ($permissions->candelete == 0 ) {
            $delsactiveclass = 'activeclass';   
        }

        if($usertype == 0){
            $hideSchedulingTeamOption = 1;
        }
        
    }
    foreach ($resultSearchStaff as $value) {
        //get user team role
        if($usertype == 0){
            $dropdwonclass = 'js_mainrole';
           
            $selectedvalue =$value['NonAdditionalRoleID']; 
        }else{
            $dropdwonclass = 'js_rota';
            $selectedvalue = $value['rota'];
        }
        
        $rolecol=  '';
       
        $basicdata = array($value['userDisplayName'],$value['StaffNumber'],$value['NetLogin']);
        if(is_null($selectedvalue)) {
            $selectedvalue = 0;
        }

        $rolecol = '<div id="unsetRotadiv" data_order_'.$selectedvalue.'>';
        if ($dropdwonclass == 'js_rota') {
            $rolecol.= '<select class="rota-btn '.$dropdwonclass.' mWH60  '.$disabled.'" name="hometeam" id="mt-0" data-setparam="1"  data-teamid="'.$schedulingTeamId.'" data-userid ="'.$value['UserID'].'"data-schedulepersonid="'.$value['ScheduledPersonID'] .'">';
        } else {
            $rolecol.= '<select class="'.$disabled.' rota-btn ' . $dropdwonclass . '" name="hometeam" id="mt-0" data-setparam="1"  data-teamid="' . $schedulingTeamId . '" data-userid ="' . $value['UserID'] . '"data-schedulepersonid="' . $value['ScheduledPersonID'] . '">';
        }
        if($usertype == 0){
            foreach($getMainRoleLists as $rolemainValue){
            $selected = '';
            if(!is_null($selectedvalue) && $rolemainValue['RoleID'] == $selectedvalue){
                $selected = 'selected';
            }
                if($hideSchedulingTeamOption && $rolemainValue['RoleID'] == 3) {
                    if ($current_netlogin === strtolower($value['NetLogin'] ?? '')) {
                        $rolecol.= '<option   value="'.$rolemainValue['RoleID'].'"'.$selected.' >'.$rolemainValue['RoleName'].'</option>';
                    }else{
                        $rolecol.= '<option disabled="disabled"  value="'.$rolemainValue['RoleID'].'"'.$selected.' >'.$rolemainValue['RoleName'].'</option>';
                    }
                } else {
                        $rolecol.= '<option   value="'.$rolemainValue['RoleID'].'"'.$selected.' >'.$rolemainValue['RoleName'].'</option>';
                }
            }
            }else{
            $rotaHideArr = array (
                0 => 'Show',
                1 => 'Hide',
                2 => 'Leave'
                );
                
            foreach($rotaHideArr as $intKey =>$strRotaStatus){
            $selected = '';
            if($intKey == $selectedvalue){
            $selected = 'selected';
            }
            $rolecol.= '<option   value="'.$intKey.'"'.$selected.' >'.$strRotaStatus.'</option>';
            }
            }

        $rolecol .='</select> </div>';
        $basicdata0 = array($rolecol,(($value['isDefault'] == 0 || $value['isDefault'] == null) ? '<span class="imgtransparent">0</span><div class="tick js_setdefaultteam staffcrossRed '.$disabled.'" data-default= "1" data-schedulepersonid="'.$value['ScheduledPersonID'] .'" data-teamid ="'.$schedulingTeamId.'"id="js_defaultteam"></div>' : '<span class="imgtransparent">1</span><div class="tick js_setdefaultteam staffcrossGreen '.$disabled.'" data-default= "0" data-schedulepersonid="'.$value['ScheduledPersonID'] .'" data-teamid ="'.$schedulingTeamId.'"id="js_defaultteam"> </div>'));
        $basicdata2 = array();
        foreach($getAdditionalRoleLists as $roleValue){
			 if(($usertype == 0) && ($roleValue['RoleName']=="Team Leader")){
				 
			 } else {
            $basicdata2[]= (($value[$roleValue['RoleName']] == 0 || $value[$roleValue['RoleName']] == null) ? '<span class="imgtransparent">0</span><div class="tick js_additionalpermissions staffcrossRed '.$disabled.'"  data-setparam = "1" data-roleid ="'.$roleValue['RoleID'].'" data-userid ="'.$value['UserID'].'"  data-teamid ="'.$schedulingTeamId.'"id="js_additionalpermissions"></div>' : '<span class="imgtransparent">1</span><div class="tick js_additionalpermissions staffcrossGreen '.$disabled.'" data-setparam = "0" data-userid ="'.$value['UserID'].'" data-roleid ="'.$roleValue['RoleID'].'" data-teamid ="'.$schedulingTeamId.'"id="js_additionalpermissions"></div>');
			 }
        }
       if($usertype == 0){
        $basicdata3 = array('<a href="javascript:void(0)" class="js_userdatainfo" data-staffid="'.$value['StaffID'].'" data-netlogin="'.$value['NetLogin'].'" data-userid ="'.$value['UserID'].'"><i class="fa fa-info-circle" id="circle-clr"></i></a>&nbsp;&nbsp;&nbsp;
        <a href="javascript:void(0)" id="js_nonscheduledhistory" data-userid ="'.$value['UserID'].'" data-teamid="'.$schedulingTeamId.'"> <i class="fa fa-hourglass-3" id="circle-clr"></i></a>','<a href="javascript:void(0)" class="anchor-colour js_removeStaff  '. $delsactiveclass.'" data-teamid = "'.$schedulingTeamId.'" data-schedulepersonid ="'.$value['ScheduledPersonID'].'">Remove</a>');
       } else{
        $basicdata3 = array((($value['IsHomeTeam'] == 0 || $value['IsHomeTeam'] == null) ? '<span class="imgtransparent">0</span><div class="tick staffcrossRed  '.$disabled.'" data-default= "1" data-schedulepersonid="'.$value['ScheduledPersonID'] .'" data-teamid ="'.$schedulingTeamId.'"id="js_hometeam"></div>' : '<span class="imgtransparent">1</span><div class="tick  staffcrossGreen" data-default= "0" data-schedulepersonid="'.$value['ScheduledPersonID'] .'" data-teamid ="'.$schedulingTeamId.'"id="js_hometeam">'),'<a href="javascript:void(0)" class="js_userdatainfo" data-staffid="'.$value['StaffID'].'" data-netlogin="'.$value['NetLogin'].'" data-userid ="'.$value['UserID'].'"></div><i class="fa fa-info-circle" id="circle-clr"></i></a>&nbsp;&nbsp;&nbsp;
        <a href="javascript:void(0)" id="js_nonscheduledhistory" data-userid ="'.$value['UserID'].'" data-teamid="'.$schedulingTeamId.'"> <i class="fa fa-hourglass-3" id="circle-clr"></i></a>');
       }
       $data[] = array_merge($basicdata,$basicdata0,$basicdata2,$basicdata3);
        $counterkey = $counterkey + 1;
    }
    
    return $data;
}
    
/*
* @Description : Create nonSchedyled team satff
* @access : Public
* @global : Not Applicable
* @param  : N/A
* @return : Array output
*/

function createNonScheduledTeamStaff($netlogin,$teamid,$moduleName = ''){
   
    $moduleName = 'NonScheduledTeamStaff';
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_mod_setNonScheduledTeamStaff] ?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $netlogin, PDO::PARAM_STR);
    $stmt->bindParam(2, $teamid, PDO::PARAM_INT);
    $stmt->bindParam(3, $this->userID, PDO::PARAM_INT);
    $stmt->bindParam(4, $moduleName, PDO::PARAM_STR);
    $stmt->bindParam(5, $this->userName , PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

/*
* @Description : Remove nonSchedyled team satff
* @access : Public
* @global : Not Applicable
* @param  : N/A
* @return : Array output
*/

function removeNonScheduledTeamStaff($personid,$teamid){
   
    
    $pdo = OpenDBLinkA7();
    $moduleName = 'NonScheduledTeamStaff';
    // Set the statement to use
    $sql = "exec [dbo].[usp_del_nonScheduledTeamSatff] ?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $personid, PDO::PARAM_INT);
    $stmt->bindParam(2, $teamid, PDO::PARAM_INT);
    $stmt->bindParam(3, $this->userID, PDO::PARAM_INT);
    $stmt->bindParam(4, $moduleName, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

/*
* @Description : Select frop down
* @access : Public
* @global : Not Applicable
* @param  : N/A
* @return : html dropdown
*/

private function setSelectDropDown($scheduledtype,$selectedvalue){
   $selectcol = '';
 
if($scheduledtype == 0){
$mainroletype = 0;
$getMainRoleLists = json_decode(getRoleLists($roleaction = '',$mainroletype),true);


foreach($getMainRoleLists as $rolemainValue){
$selected = '';
if(!is_null($selectedvalue) && $rolemainValue['RoleID'] == $selectedvalue){
    $selected = 'selected';
}
$selectcol.= '<option   value="'.$rolemainValue['RoleID'].'"'.$selected.' >'.$rolemainValue['RoleName'].'</option>';
}
}else{
$rotaHideArr = array (
    0 => 'Show',
    1 => 'Hide',
    2 => 'Leave'
    );
    
foreach($rotaHideArr as $intKey =>$strRotaStatus){
$selected = '';
if($intKey == $selectedvalue){
$selected = 'selected';
}
$selectcol.= '<option   value="'.$intKey.'"'.$selected.' >'.$strRotaStatus.'</option>';
}
}

//return $selectcol;
}


function getUserIdbyNetLogin($netlogin){

    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "Select UD_UserID from UserDetails Where UD_NetLogin = ?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $netlogin , PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

}



<?php
include_once '../../../function-includes/DBHelper.php';

class classContractHistory
{
    /*
    * @Description : Fetch all contract history for scheduled person.
	* @access : Public
	* @global : Not Applicable
	* @param  : $staffid
	* @return : JSON output
    */
    function getContractHistory($staffid)
    {
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_get_ContractHistory] ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $staffid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($result) < 1)
        {
            $data[] = array('','','','','','','','','','No Record Found','','','','','','','','','','','','','','','');
        } else {
            foreach ($result as $value) {
                $AutoEDPTOIL=null;if ($value['AutoEDPTOIL']==1) { $AutoEDPTOIL='Yes';} elseif ($value['AutoEDPTOIL']==0){$AutoEDPTOIL='No'; }
                $spanstart = $spanend ='';
                $spanstartfirstcol ='<span class="customdateSort">'.date('Ymd',strtotime($value['Startdate'])).'</span>';
                $spanendfirstcol ='';
                $spanstartsecondcol ='<span class="customdateSort">'.date('Ymd',strtotime($value['Enddate'])).'</span>';
                $spanendsecondcol ='';
                if(($value['ContractIsActiveVal'] == 0) || ($value['ConfigIsActiveVal'] == 0)){
                       $spanstart = '<div class="contarctahistoryInActive">'; 
                       $spanend = '</div>'; 
                       
                }
				$value['ImportedHistory'] = isset($value['ImportedHistory']) ? $value['ImportedHistory'] : '';
                $data[] = array(
                    $spanstart.$spanstartfirstcol .date('d-m-Y',strtotime($value['Startdate'])).$spanendfirstcol.$spanend,
                    $spanstart.$spanstartsecondcol.date('d-m-Y',strtotime($value['Enddate'])).$spanendsecondcol.$spanend,
                    $spanstart.$value['AccountingGroup'].$spanend,
                    $spanstart.$value['EDPMinimumExcBreaks'].$spanend,
                    $spanstart.$value['PaidContract'].$spanend,
                    $spanstart.(($value['ManualEDP'] == 1) ? 'True' : 'False').$spanend,
                    $spanstart.$value['EFT'].$spanend,
                    $spanstart. $value['EmployeeGroup'].$spanend,
                    $spanstart.$value['ContractType'].$spanend,
                    $spanstart.$value['PaymentType'].$spanend,
                    $spanstart.$value['TeamPayDepartment'].$spanend,
                    $spanstart.$AutoEDPTOIL.$spanend,
                    $spanstart.$value['TermsConditions'].$spanend,
                    $spanstart.$value['CostCode'].$spanend,
                    $spanstart.$value['ActivityType'].$spanend,
                    $spanstart.$value['duty_duration'].$spanend,
                    $spanstart.$value['Title'].$spanend,
                    $spanstart.$value['StaffNumber'].$spanend,
                    $spanstart.$value['network_id'].$spanend,
                    $spanstart.$value['first_name'].$spanend,
                    $spanstart.$value['Surname'].$spanend,
                    $spanstart.$value['middle_name'].$spanend,
                    $spanstart.$value['PreferredForename'].$spanend,
                    $spanstart.$value['Designation'].$spanend,
                    $spanstart.'<a href="javascript:void(0)" id="js_contactHistory" data-msg="'.base64_encode($value['ImportedHistory']).'"  onClick="showContractHistory(this);" ><i class="fa fa-hourglass-3" id="circle-clr"></i></a>'.$spanend
                );
            }
        }
       
      $history = array('data' => $data);
      echo json_encode($history);
    }
}

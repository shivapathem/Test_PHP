<?php
include_once '../../../../function-includes/DBHelper.php';
class ClassDivisions
{

    public  $userID;
    public  $userName;
    public  $moduleName;
    public  $cureentaDate;

    public function __construct()
    {
        $this->userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
        $this->userName = $_SESSION['user']["FullName"];
        $this->moduleName = 'Divisions';
        $this->cureentaDate = date("d-m-Y");
    }

    /*
    * @Description : Fetch all of the accessible scheduling team for a user.
	* @access : Public
	* @global : Not Applicable
	* @param  : $intuserid
	* @return : JSON output
    */
    function getDivisionsList($divisionid = 0)
    {
        $pdo = OpenDBLinkA7();
		$whereCond = (!empty($divisionid) && $divisionid > 0) ? 'Where DivisionID = ?' : '';
        $sql = "Select DivisionID,DivisionName,Notes,isActive,EffectedFrom from Divisions $whereCond order by DivisionName";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $divisionid, PDO::PARAM_INT);
        $stmt->execute();
        if ($divisionid == 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return json_encode($result);
    }

    /*
    * @Description : check the divisionname is exist or not
	* @access : Public
	* @global : Not Applicable
	* @param  : $divisionid,$divisionname
	* @return : string return and boolean
    */
    function checkDivisionNameValidation($divisionid, $divisionname)
    {

        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            $sql = "SELECT 1 FROM Divisions where DivisionName = ? and DivisionID <> ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $divisionname, PDO::PARAM_STR);
            $stmt->bindParam(2, $divisionid, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($result) && count($result) > 0) {
                return 'This Area Name Already Exists, Please Try with Other Area Name';
            } else {

                return 'true';
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /*
     * @Description : insert/update division record
     * @access : Public
     * @global : Not Applicable
     * @param  : $params
     * @return : string return and boolean
     */
    function insertUpdateDivision($params)
    {

        try {
            $params['divisionname'] = trim($params['divisionname']);
            $status = $returnstring = '';
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_mod_Divisionrecord] ?,?,?,?,?,?,?,?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $params['divisionid'], PDO::PARAM_INT);
            $stmt->bindParam(2, $params['action'], PDO::PARAM_STR);
            $stmt->bindParam(3, $params['divisionname'], PDO::PARAM_STR);
            $stmt->bindParam(4, $params['isactive'], PDO::PARAM_INT);
            $stmt->bindParam(5, $params['notes'], PDO::PARAM_STR);
            $stmt->bindParam(6, $this->moduleName, PDO::PARAM_STR);
            $stmt->bindParam(7, $this->userID, PDO::PARAM_INT);
            $stmt->bindParam(8, $this->userName, PDO::PARAM_STR);
            $stmt->bindParam(9, $status, PDO::PARAM_STR);
            $stmt->bindParam(10, $returnstring, PDO::PARAM_STR);

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return json_encode($result);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}

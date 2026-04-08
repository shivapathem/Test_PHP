<?php
include_once '../../../../function-includes/DBHelper.php';
session_start();

class ClassHolidays
{
	public function __construct()
    {
        $this->userID = $_SESSION['user']["UserID"];
        $this->userName = $_SESSION['user']["FullName"];
    }

    /*
    * @Description : Fetch all public holidays.
	* @access : Public
	* @global : Not Applicable
	* @param  : NA
	* @return : JSON output
    */
    function getHolidaysList($id = 0)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_PublicHolidaysrecords] ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($id == 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return json_encode($result);
    }

     /*
     * @Description : insert/update holidays record
     * @access : Public
     * @global : Not Applicable
     * @param  : $params
     * @return : string return and boolean
     */
    function insertUpdateHoliday($params)
    {
        try {
            $status = $returnstring = '';
            $isactive = 1;
            $modifiedby = $params['id'] > 0 ? $this->userID : 0 ;
            $holidaydate = date('Y-m-d',strtotime($params['holidaydate']));
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_mod_Publicholidays] ?,?,?,?,?,?,?,?,?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $params['id'], PDO::PARAM_INT);
            $stmt->bindParam(2, $params['action'], PDO::PARAM_STR);
            $stmt->bindParam(3, $params['calenderyear'], PDO::PARAM_STR);
            $stmt->bindParam(4, $params['description'], PDO::PARAM_STR);
            $stmt->bindParam(5, $holidaydate, PDO::PARAM_STR);
            $stmt->bindParam(6, $params['week'], PDO::PARAM_STR);
            $stmt->bindParam(7, $isactive, PDO::PARAM_INT);
            $stmt->bindParam(8, $this->userID, PDO::PARAM_INT);
            $stmt->bindParam(9, $modifiedby, PDO::PARAM_INT);
            $stmt->bindParam(10, $status, PDO::PARAM_STR);
            $stmt->bindParam(11, $returnstring, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return json_encode($result);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /*
     * @Description : Change Holiday record
     * @access : Public
     * @global : Not Applicable
     * @param  : $params
     * @return : string return and boolean
     */
    function activeInactiveHoliday($params)
    {
        try {
            $status = $returnstring = '';
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_del_Holiday] ?,?,?,?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $params['id'], PDO::PARAM_INT);
            $stmt->bindParam(2, $this->userID, PDO::PARAM_INT);
            $stmt->bindParam(3, $params['action'], PDO::PARAM_STR);
            $stmt->bindParam(4, $params['currentstatus'], PDO::PARAM_INT);
            $stmt->bindParam(5, $status, PDO::PARAM_INT);
            $stmt->bindParam(6, $returnstring, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return json_encode($result);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /*
     * @Description : Delete Holiday record
     * @access : Public
     * @global : Not Applicable
     * @param  : $params
     * @return : string return and boolean
     */
    function deleteHoliday($params)
    {
        try {
            $status = $returnstring = '';
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_del_Holiday] ?,?,?,?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $params['id'], PDO::PARAM_INT);
            $stmt->bindParam(2, $this->userID, PDO::PARAM_INT);
            $stmt->bindParam(3, $params['action'], PDO::PARAM_STR);
            $stmt->bindParam(4, $params['currentstatus'], PDO::PARAM_INT);
            $stmt->bindParam(5, $status, PDO::PARAM_INT);
            $stmt->bindParam(6, $returnstring, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return json_encode($result);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function getYearDropdownList(){
        $currentyear = date("Y");
        $start = $currentyear - 7;
        $endyear = $currentyear + 7;
        $years = array();
        for($i = $start; $i <= $endyear; $i++){
            $years[] = $i;
        }
        return $years;

    }
}
<?php
include_once '../../../../function-includes/DBHelper.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class classColorMaster
{
    public $userID = '';
    public $userName = '';
    public $moduleName = '';
    public $cureentaDate = '';

    public function __construct()
    {
        $this->userID = $_SESSION['user']["UserID"];
        $this->userName = $_SESSION['user']["FullName"];
        $this->moduleName = 'AreaAdmin';
        $this->cureentaDate = date("d-m-Y");
    }

    /*
    * @Description : Fetch all of the Master colour Records.
	* @access : Public
	* @global : Not Applicable
	* @param  : $colorId This param contains the colour ID if want whole list then pass 0 Otherwise for specifi row pass colour ID
	* @return : JSON output
    */
    function getColorsList($colorId = 0)
    {
       $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_MasterColorRecords] ?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        
        $stmt->bindParam(1, $colorId, PDO::PARAM_INT);
        $stmt->bindParam(2, $sessUserId, PDO::PARAM_INT);
        $stmt->execute();
        if ($colorId == 0) {
            return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            return json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        }
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
    function insertUpdateColour($params)
    {
        try {
            $status = $returnstring = '';
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_mod_MasterColorRecords] ?,?,?,?,?,?,?,?,?,?,?,?,?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $params['colourid'], PDO::PARAM_INT);
            $stmt->bindParam(2, $params['actionname'], PDO::PARAM_STR);
            $stmt->bindParam(3, $params['divisionid'], PDO::PARAM_INT);
            $stmt->bindParam(4, $params['colourname'], PDO::PARAM_STR);
            $stmt->bindParam(5, $params['colournotes'], PDO::PARAM_STR);
            $stmt->bindParam(6, $params['backgroundcolour'], PDO::PARAM_STR);
            $stmt->bindParam(7, $params['fontcolour'], PDO::PARAM_INT);
            $stmt->bindParam(8, $params['isdefault'], PDO::PARAM_INT);
            $stmt->bindParam(9, $params['isactive'], PDO::PARAM_INT);
            $stmt->bindParam(10, $params['username'], PDO::PARAM_STR);
            $stmt->bindParam(11, $params['createdby'], PDO::PARAM_INT);
            $stmt->bindParam(12, $params['modifyby'], PDO::PARAM_INT);
            $stmt->bindParam(13, $status, PDO::PARAM_STR);
            $stmt->bindParam(14, $returnstring, PDO::PARAM_STR);

            $stmt->execute();
            return json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function getUserDivisions(){
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_DivisionsByUserType] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $this->userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($result)){
            return $result;
        } else {
            return array();
        }
    }
}

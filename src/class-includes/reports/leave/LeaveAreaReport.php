<?php

class LeaveAreaReoprt {

    protected $userID, $netLogin;

    public function __construct()
    {
        $this->userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
        $this->netLogin =  isset($_SESSION['user']['user'])  && ($_SESSION['user']['user'] != '') ? $_SESSION['user']['user'] :$_COOKIE['editWeeklyUserNetLogin'];
    }

    function getLeaveAreaReport(int $leaveYear) {
        return $this->spLeaveAreaReport($leaveYear, $this->netLogin);
    }

    function spLeaveAreaReport(int $leaveYear, string $netLogin) {
        $pdo = OpenDBLinkA7();
    
        $query = "exec [dbo].[usp_LeaveBalanceByAreaReport] :leaveYear, :netLogin";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':leaveYear', $leaveYear, PDO::PARAM_INT);
        $stmt->bindParam(':netLogin', $netLogin, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchall(PDO::FETCH_ASSOC);
    }
}
<?php
include_once 'DBHelper.php';

function GetUserFavouritesInGroup ($intID) {
    $pdo = OpenDBLinkA7();

    $sql = "exec [dbo].[usp_get_UserFavouritesInGroup] ? ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $intID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    $arrFavs = array();

    foreach ($result as $row) {              
        $arrFavs['Users'][$row["staffnumber"]] = $row["FullName"];
        $arrFavs['Team'] = $row["TeamID"];
    }
    return $arrFavs;
}

    function GetUserFavouritesNotInGroup ($intTeamID, $userId = 0) {
    
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_get_UserFavouritesNotInGroup] ?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->bindParam(2, $intTeamID, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $arrFavs = array();

        foreach ($result as $row) {   
            if($row["FullName"])
			{
				$arrFavs[$row["StaffNumber"]] = $row["FullName"];
			}
        }

        return($arrFavs);
    }  

    function GetTeamNameByID ($intID) {
        $pdo = OpenDBLinkA7();

        $strQuery = "SELECT     schedulingTeamName
                     FROM       schedulingTeams (NOLOCK)
                     WHERE      (schedulingTeamId = $intID)";

        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if(isset($result['schedulingTeamName']))
		{
            return $result['schedulingTeamName'];
		}else
		{
            return '';
		}
      
    }

    function GetGroupListByLogin ($strUser) {
        $pdo = OpenDBLinkA7();

        $strQuery = "SELECT        id, description
          FROM          user_favourites
          WHERE         (Login = N'$strUser')
          ORDER BY      description";

        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      
    }

    function GetWeeklyFilterOption ($strUser) {
        $pdo = OpenDBLinkA7();

        $strQuery = "SELECT      isnull(WeeklyFilterOption, 0) as WeeklyFilterOption
        FROM            User_Web_Config
        WHERE        (Login = N'$strUser')";

        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
      
    }

    function InsertWeeklyFilterOption($strUser, $intOption) {
        try {
            $pdo = OpenDBLinkA7();

            $strQuery = "IF EXISTS (SELECT       ID
            FROM         User_Web_Config
            WHERE        (Login = N'$strUser'))

            UPDATE       User_Web_Config
            SET          WeeklyFilterOption = $intOption
            WHERE        (Login = N'$strUser')
            ELSE
            INSERT INTO  User_Web_Config
                        (WeeklyFilterOption, Login)
            VALUES       ($intOption, N'$strUser')";

            $stmt = $pdo->prepare($strQuery);
            $stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('pdo exception ', (array) $e);
        }
    }
?>
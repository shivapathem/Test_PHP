<?php
include_once "DBHelper.php";

function GetUserSettingsByTeam($strLogin) {
  $pdo = OpenDBLinkA7();
  
  $strQuery = "SELECT COUNT(skills_programmes_staff_link.programmes_id) AS CountSkills 
				FROM skills_programmes_staff_link 
				INNER JOIN UserDetails ON UD_UserID = UserID 
				WHERE (UD_NetLogin = N'$strLogin')";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC); 
  $arrUser["SkillsCount"] = $row["CountSkills"];
  
  return ($arrUser);
}
<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$pdo = OpenDBLinkA7();
$NetLoginId = isset($_SESSION['user']['user'])  && ($_SESSION['user']['user'] != '') ? $_SESSION['user']['user'] :$_COOKIE['editWeeklyUserNetLogin'];
$searchinguser="0";

if(isset($_REQUEST) && !empty($_REQUEST))
{
  $keyslist = array_keys($_REQUEST);
  $arrayKeyfound = in_array('ChooseUser',$keyslist);

  if ($arrayKeyfound) {
    $searchinguser = $_REQUEST['ChooseUser'];
  }
    $sr=1;
    $filterkey='';
    foreach($_REQUEST as $key=>$value)
    {
	  if(!in_array($key, array('encodeData', 'encodeParam', 'encodeParamTime')))
	  {
		  $filterkey.=$key.'='.$value;
		  if ($sr != sizeof($_REQUEST))
			{
			  $filterkey.='&';
			}      
		  $sr++;
	  }
    }

    //Check For Filter Exists//
    $filterRows=null;
    try {
      $strQuery = "SELECT ID,UsedOnce,DataDefault FROM yearlyStaffFilter WHERE SearchBy='$NetLoginId'";
      $stmt = $pdo->prepare($strQuery);
      $stmt->execute();
      $filterRows =  $stmt->fetch(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
      logger()->critical('DB error', (array)$e);
    }
    //Check Filter Exists End //

    if (!empty($filterRows)) {
      if($searchinguser !== "0")
      {
        try {
			$filterkey = rtrim($filterkey, '&');
          $strQuery2 = "UPDATE yearlyStaffFilter SET DataInput=:filterkey,DataDefault=:dfilterkey, UsedOnce=1 WHERE SearchBy=:NetLoginId";
          $stmt = $pdo->prepare($strQuery2);
          $stmt->bindValue(':NetLoginId', $NetLoginId, PDO::PARAM_STR);
          $stmt->bindValue(':filterkey', $filterkey, PDO::PARAM_STR);
          $stmt->bindValue(':dfilterkey', $filterkey, PDO::PARAM_STR);
          $stmt->execute();
          } catch (PDOException $e) {
          logger()->critical('DB error', (array)$e);
        }
      }else {
        try {
          $strQuery2 = "DELETE FROM yearlyStaffFilter WHERE SearchBy=:NetLoginId";
          $stmt = $pdo->prepare($strQuery2);
          $stmt->bindValue(':NetLoginId', $NetLoginId, PDO::PARAM_STR);
          $stmt->execute();
          } catch (PDOException $e) {
          logger()->critical('DB error', (array)$e);
        }
      }
    } else {
      try {
          $dfilterkey = '';
          $dfilterkey.=$filterkey.'&filter_pending=on&filter_approved=on&filter_agreed=on';
          $strQuery3= "INSERT INTO yearlyStaffFilter (DataInput,DataDefault,SearchBy) values(:filterkey,:dfilterkey,:NetLoginId)";
          $stmt = $pdo->prepare($strQuery3);
          $stmt->bindValue(':filterkey', $filterkey, PDO::PARAM_STR);
          $stmt->bindValue(':dfilterkey', $dfilterkey, PDO::PARAM_STR);
          $stmt->bindValue(':NetLoginId', $NetLoginId, PDO::PARAM_STR);
          $stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('DB error', (array)$e);
        }
    }
  

  
}

?>
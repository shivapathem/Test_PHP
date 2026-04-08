<?php
$arrStatus = array ( 
      0  => 'Requested',
      1  => 'In Progress',
      2  => 'Completed',
      3  => 'Declined',
      4  => 'Deleted'                                                
   );


function GetSuggestions($intTypeID, $intPageType = 0) {
  $db = OpenDatabase();
  // ############################################################################ Get the defaults ############################################################################
  $strQuery = "SELECT          WebSiteSuggestions.ID, CASE WHEN staff.login IS NULL THEN Staff_Guests.Forename + N' ' + Staff_guests.Surname ELSE Staff.Forename + N' ' + Staff.Surname END AS CreatorName, 
                               WebSiteSuggestions.DateCreated, WebSiteSuggestions.Subject, WebSiteSuggestions.Details, WebSiteSuggestions.BusinessJustification, WebSiteSuggestions.Progress, 
                               WebSiteSuggestions.History, WebSiteSuggestions.Status, WebSiteSuggestions.RemedyRef, WebSiteSuggestions.CreatorLog1n
               FROM            WebSiteSuggestions 
               LEFT OUTER JOIN Staff_Guests ON WebSiteSuggestions.CreatorLog1n = Staff_Guests.Login 
               LEFT OUTER JOIN Staff ON WebSiteSuggestions.CreatorLog1n = Staff.Login
               WHERE           TypeID = $intTypeID";
               
               
               
               
               
switch ($intPageType) {
  case 2:
    $strQuery.= " AND        (dbo.WebSiteSuggestions.Status = 2)";    
    break;
  case 3:
    $strQuery.= " AND        (dbo.WebSiteSuggestions.Status = 3)";    
    break;
  default:
    case 2:
    $strQuery.= " AND        (dbo.WebSiteSuggestions.Status < 2)";    
    break;
}    
         
  $rsSuggestions = sqlsrv_query($db, $strQuery);
  while($row = sqlsrv_fetch_array($rsSuggestions)){
  
    $arrSuggestions[$row['ID']]['Creator'] = $row['CreatorName'];
    $arrSuggestions[$row['ID']]['CreatorLog1n'] = $row['CreatorLog1n'];
    if (is_null($row['DateCreated'])) {
      $arrSuggestions[$row['ID']]['uDateCreated'] = 0;
      $arrSuggestions[$row['ID']]['DateCreated'] = '';
    }
    else {
      $arrSuggestions[$row['ID']]['uDateCreated'] = strtotime($row['DateCreated']->format("Y-m-d H:i"));   
      $arrSuggestions[$row['ID']]['DateCreated'] = $row['DateCreated']->format("jS M Y");
    }
    $arrSuggestions[$row['ID']]['Subject'] = $row['Subject'];        
    $arrSuggestions[$row['ID']]['Details'] = $row['Details']; 
    $arrSuggestions[$row['ID']]['RemedyRef'] = $row['RemedyRef']; 
    $arrSuggestions[$row['ID']]['Progress'] = $row['Progress']; 
    $arrSuggestions[$row['ID']]['BusinessJustification'] = $row['BusinessJustification']; 
    $arrSuggestions[$row['ID']]['History'] = $row['History'];             
    $arrSuggestions[$row['ID']]['Status'] = $row['Status'];  
  }
  if (isset($arrSuggestions)) {
    return ($arrSuggestions);
  }
}
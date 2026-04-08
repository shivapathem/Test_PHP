<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';

$intTypeID = $_REQUEST['typeid'];
$arrBreak =  GetBreakTypeDesc($intTypeID);
$strTypeName = $arrBreak['Description'];

$arrBreaks = GetBreaksByType($intTypeID);

echo '<div class="tableheadersmall medtextboldcentre" style="width:410px; position:relative">';
echo '<br>Breaks Table for '.$strTypeName.'<br>Double-Click to edit<br><br>';
echo '<div class="DutyCellBottomRight"><img border="0" src="images/add.png" width="16" height="16" Style="cursor: pointer" onclick="javascript:NewEditBreak(0, '.$intTypeID.')";></div>';
echo '</div>';

echo '<table class="tablesmalltidy">';   
echo '<tr height="40px">';  
echo '<th width="200px">';  
echo 'Effective From';
echo '</th>';
echo '<th width="100px">';  
echo 'Duration'; 
echo '</th>'; 
echo '<th width="100px">';  
echo 'Breaks'; 
echo '</th>'; 
echo '</tr>';
if (isset($arrBreaks[$intTypeID])) {
  foreach ($arrBreaks[$intTypeID] as $intBreakID => $arrBreak ) {
    echo '<tr height="30px" class="handcursor" onclick="javascript:NewEditBreak('.$intBreakID.', '.$intTypeID.')";>';
    echo '<td>';
    echo date("jS F Y", strtotime($arrBreak['StartDate']));
    echo '</td>';
    echo '<td>';
    echo $arrBreak['Hours'];
    echo '</td>';
    echo '<td>';
    echo $arrBreak['BreakTime'];
    echo '</td>';
    echo '</tr>';
  }
}
echo '</table>';






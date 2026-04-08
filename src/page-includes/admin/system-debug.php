<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
if ($intSysAdmin == 1 || !empty($userDivisionsList)) {

  echo '<table id="variables" class="tablesmalltidy" width="100%">';
  echo '<thead>';
  echo '<tr>';
  echo '<th width="300px">Variable</th>';
  echo '<th>Value</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  
  echo '<tr>';
  echo '<td>';  
  echo 'Database Host';
  echo '</td>';
  echo '<td>';  
  echo getenv('ALLOCATE_DB_HOST');
  echo '</td>';       
  echo '</tr>';  
  
  echo '<tr>';
  echo '<td>';  
  echo 'Database Name';
  echo '</td>';
  echo '<td>';  
  echo getenv('ALLOCATE_DB_NAME');
  echo '</td>';       
  echo '</tr>';

  echo '<tr>';
  echo '<td>';  
  echo 'SMTP_Host';
  echo '</td>';
  echo '<td>';  
  echo getenv('SMTP_HOST');
  echo '</td>';       
  echo '</tr>';
  
  echo '<tr>';
  echo '<td>';  
  echo 'Base Directory';
  echo '</td>';
  echo '<td>';  
  echo getenv('BASE_DIR');
  echo '</td>';       
  echo '</tr>';
  
  echo '<tr>';
  echo '<td>';  
  echo 'Base URL';
  echo '</td>';
  echo '<td>';  
  echo getenv('BASE_URL');
  echo '</td>';       
  echo '</tr>';  
  
  echo '<tr>';
  echo '<td>';  
  echo 'Documents URL';
  echo '</td>';
  echo '<td>';  
  echo getenv('DOCS_URL');
  echo '</td>';       
  echo '</tr>';    
  
  echo '<tr>';
  echo '<td>';  
  echo 'Public Directory';
  echo '</td>';
  echo '<td>';  
  echo getenv('PUBLIC_DIR');
  echo '</td>';       
  echo '</tr>';   
  
  echo '<tr>';
  echo '<td>';  
  echo 'Staff Images Directory';
  echo '</td>';
  echo '<td>';  
  echo getenv('STAFF_IMAGE_UPLOAD_DIR');
  echo '</td>';       
  echo '</tr>';   
  
  echo '<tr>';
  echo '<td>';  
  echo 'Staff Images URL';
  echo '</td>';
  echo '<td>';  
  echo getenv('STAFF_IMAGE_URL');
  echo '</td>';       
  echo '</tr>';    
  
  echo '<tr>';
  echo '<td>';  
  echo 'iCal Directory';
  echo '</td>';
  echo '<td>';  
  echo getenv('ICS_DIR');
  echo '</td>';       
  echo '</tr>';   
  
  echo '<tr>';
  echo '<td>';  
  echo 'eMail BCC Address';
  echo '</td>';
  echo '<td>';  
  echo getenv('EMAIL_BCC');
  echo '</td>';       
  echo '</tr>';   
  
  echo '<tr>';
  echo '<td>';  
  echo 'eMail CSS';
  echo '</td>';
  echo '<td>';  
  echo getenv('EMAIL_CSS');
  echo '</td>';       
  echo '</tr>';    
  
  echo '<tr>';
  echo '<td>';  
  echo 'eMail Banner';
  echo '</td>';
  echo '<td>';  
  echo getenv('PO_BANNER');
  echo '</td>';       
  echo '</tr>';  
  
  echo '<tr>';
  echo '<td>';  
  echo 'eMail BBC Logo';
  echo '</td>';
  echo '<td>';  
  echo getenv('BBC_LOGO');
  echo '</td>';       
  echo '</tr>';    
  echo '</table>';
  } else {
    echo 'Access Denied'; die;
}
  
$indicesServer = array('PHP_SELF',
'argv',
'argc',
'GATEWAY_INTERFACE',
'SERVER_ADDR',
'SERVER_NAME',
'SERVER_SOFTWARE',
'SERVER_PROTOCOL',
'REQUEST_METHOD',
'REQUEST_TIME',
'REQUEST_TIME_FLOAT',
'QUERY_STRING',
'DOCUMENT_ROOT',
'HTTP_ACCEPT',
'HTTP_ACCEPT_CHARSET',
'HTTP_ACCEPT_ENCODING',
'HTTP_ACCEPT_LANGUAGE',
'HTTP_CONNECTION',
'HTTP_HOST',
'HTTP_REFERER',
'HTTP_USER_AGENT',
'HTTPS',
'REMOTE_ADDR',
'REMOTE_HOST',
'REMOTE_PORT',
'REMOTE_USER',
'REDIRECT_REMOTE_USER',
'SCRIPT_FILENAME',
'SERVER_ADMIN',
'SERVER_PORT',
'SERVER_SIGNATURE',
'PATH_TRANSLATED',
'SCRIPT_NAME',
'REQUEST_URI',
'PHP_AUTH_DIGEST',
'PHP_AUTH_USER',
'PHP_AUTH_PW',
'AUTH_TYPE',
'PATH_INFO',
'ORIG_PATH_INFO') ;
echo '<br>';
echo '<table class="tablesmalltidy" width="100%">';
echo '<tr><th colspan="2">$_SERVER Variables</th></tr>' ;
echo '<tr><th width="300px">Variable</th><th>Value</th></tr>' ;



foreach ($indicesServer as $arg) {
    if (isset($_SERVER[$arg])) {
        echo '<tr><td width="300px">'.$arg.'</td><td>' . $_SERVER[$arg] . '</td></tr>' ;
    }
    else {
        echo '<tr><td width="300px">'.$arg.'</td><td>-</td></tr>' ;
    }
}
echo '</table>' ;    
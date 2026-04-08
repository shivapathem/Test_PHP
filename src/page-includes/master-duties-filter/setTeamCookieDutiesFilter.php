<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../class-includes/userRolePermissions.php';

//Check User Authentication
$pageid = 4;//Master duties form id
if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $permissions = getUserRoleByTeam($pageid, $_COOKIE["masterdutyteams"]);
    
    if(empty($permissions->canview)){?>
        <script>
          $(document).ready(function () {
             $.cookie("masterdutyteams", 0, { path: '/' });
          });
    </script>
    <?php 
    $_COOKIE["masterdutyteams"]=0;
    }
}

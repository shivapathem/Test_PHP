<?php
//Listing of Holidays
include_once '../../../../function-includes/testaccess.php';
include_once '../process/classHolidays.php';
include_once '../../../../class-includes/userRolePermissions.php';

//call the class object
$holidayobj = new ClassHolidays;
//defien the variable
$pageid = 10;
$holidayLists = json_decode($holidayobj->getHolidaysList(0), true);
// Call User Permission function.
$disabled = '';
$permissions = getUserRolePermissions($pageid);
if ($permissions->canmodify == 0) {
    $disabled = 'notclickable';
}
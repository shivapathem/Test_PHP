<?php
/*  Scheduled people sort code Form In Popup*/
use Symfony\Component\HttpFoundation\Request;
require_once __DIR__ ."/../../../../../vendor/autoload.php";
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";
$request = Request::createFromGlobals();
$weeknumber_array =  explode("/",$request->get('weekNumber'));
$weeknumber = $weeknumber_array[1].$weeknumber_array[0];
?>
<form id="scheduledpersonsortcode" method="post" >
  <table id="scheduledpersonsortcodeable" class="smalltable bluetable" width="100%">
    <thead><tr><th colspan="5">Change Sort Code Only </th></tr></thead>
    <tbody>
      <tr><td class="lightblue">Name</td><td><input class="W-100" id="scheduledpersonname" readonly name="scheduledpersonname" type="text"  value="<?php echo $request->get('scheduledPersonName');?>"></td></tr>
      <tr>
      <tr><td class="lightblue">Sort Code</td><td><input class="W-42" id="allocsortcode" maxlength="20" name="sortcode" type="text" class="width-60" value="<?php echo $request->get('sortCode');?>"></td></tr></tr>
    </tbody>
  </table>
    <input type="hidden"  name="schedulingPersonId" value="<?php echo $request->get('schedulingPersonID');?>">
    <input type="hidden" name="js_weeknumber" value="<?php echo $weeknumber;?>">
    <input type="hidden" name="schedulingteamId" value="<?php echo $request->get('teamId');?>">
    <input type="hidden" name="allocateId" value="<?php echo $request->get('id');?>">
    <span class="sortcode_btn"><input name="js_saveSortCode" id="js_saveSortCode" type="submit" value="Save"></span>
</form>

<?php
session_start();
//Scheduled Person 
include_once '../../../../function-includes/testaccess.php';
include_once '../process/classHolidays.php';
include_once '../../../../class-includes/userRolePermissions.php';

//call the class object
$holidayobj = new ClassHolidays;
$calenderYears = $holidayobj->getYearDropdownList();
//defien the variable
$pageid = 10;
$holidayLists = json_decode($holidayobj->getHolidaysList(), true);
// Call User Permission function.
$activeclass = $disabled  = 'noclass';
$permissions = getUserRolePermissions($pageid);
if ($permissions->canmodify == 0) {
    $activeclass = 'activeclass';
    $disabled = 'notclickable';
}
if ($permissions->cancreate == 0) {
    $activeclass = 'activeclass';
}
?>
<script src="js/holiday.js"></script>
<div class="Searchcontainer p-15">
    <div class="fieldsection p-5">
      <button class="btn-class addbtn holyday-addbtn"  id="addbtn">
        <img src="images/button_add.png" alt="">&nbsp;Add New
      </button>
        <h1  class="headertextallpages  main-heading p-20">Public Holidays</h1>
      </div>
    <section>
      <div class="tables  holiday-table-h border-0">
        <div class="scrollable h-600">
          <table class="oddevenclass tablesmall stripe  dataTable no-footer" id="holidaylisting"
            style="width: 100%;" role="grid">
            <thead>
              <th class="p-tr">Calendar Year </th>
              <th class="p-tr">Date</th>
              <th class="p-tr">Week</th>
              <th class="p-tr">Description</th>
              <th class="p-tr">Active/Inactive</th>
              <th class="p-tr">Actions</th>
            </thead>
            <tbody class="context-menu-one" id="">
                        <?php if (!empty($holidayLists)) {
                            foreach ($holidayLists as $holiday) { ?>
                                <tr id=<?php echo $holiday['PublicHolidayId']; ?>>
                                    <td id="divisionname_<?php echo $holiday['PublicHolidayId']; ?>"><?php echo $holiday['CalenderYear']; ?></td>
                                    <td><?php echo date('d-m-Y',strtotime($holiday['HolidayDate'])); ?></td>
                                    <td><?php echo $holiday['Week']; ?></td>
                                    <td><?php echo $holiday['Description']; ?></td>
                                    <td>
                                      <?php 
                                      if(new DateTime(date('d-m-Y',strtotime($holiday['HolidayDate']))) > new DateTime(date('d-m-Y'))) {
                                      ?>
                                        <input class="holidaystatus isactive_<?php echo $holiday['PublicHolidayId'];?>  <?php echo $disabled; ?>"
                                        type="checkbox"  value="<?php echo $holiday['IsActive'] ?>" <?php echo $isactive = $holiday['IsActive'] == 1 ? 'checked' : '' ?>
                                        id="checkbox-btn"
                                        data-id= <?php echo $holiday['PublicHolidayId']; ?>>
                                      <?php } else {?>
                                        <input class="holidaystatus isactive_<?php echo $holiday['PublicHolidayId'];?>  <?php echo $disabled; ?>"
                                        type="checkbox" disabled="disabled"  value="<?php echo $holiday['IsActive'] ?>" <?php echo $isactive = $holiday['IsActive'] == 1 ? 'checked' : '' ?>
                                        id="checkbox-btn"
                                        data-id= <?php echo $holiday['PublicHolidayId']; ?>>
                                      <?php }?>
                                    </td>
                                    <td>
                                      <?php 
                                      if(strtotime($holiday['HolidayDate']) > strtotime(date('d-m-Y'))) {
                                      ?>
                                        <span><a  class="editaction btn-edit <?php echo $activeclass; ?>" data-PublicHolidayId="<?php echo $holiday['PublicHolidayId']; ?>" href="javascript:void(0);">
                                                    <em class="fa fa-edit"></em></a>&nbsp;&nbsp;&nbsp;
                                                    <a  class="btn-del <?php echo $activeclass; ?>" holidayid="<?php echo $holiday['PublicHolidayId']; ?>" href="javascript:void(0);">
                                                        <em class="fa fa-trash"></em>
                                                    </a>
                                                </span>
                                        <?php } else {?>
                                          <span>&nbsp;</span>
                                        <?php }?>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="filter_data_unavailabl">No records to display.</td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
          </table>
        </div>
      </div>
    </section>
    <div id="myModal" class="modal">
      <!-- Modal content -->
      <div class="modal-content p-7"><span class="closebox">×</span>
        <!--Model content-->
        <div> 
            <form method="post" id="holidayform">
          <table id="rotanewedittable" class="smalltable bluetable" width="100%">
            <thead>
              <tr>
                <th id="title" colspan="5">Public Holidays</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="lightblue" width="15%"><label for="sortcode">Calendar Year<span id="Addlabelstartdate" class="required">*</span> </label></td>
                <td width="35%">
                  <select name="calenderyear" class="w-135" id="calenderyear" onchange="setCalendarOption(this.value);">
                    <option value="">--Select Year--</option>
                    <?php foreach($calenderYears as $year):?>
                    <option <?php echo $year == date('Y') ? 'selected': '';?> value="<?php echo $year;?>"><?php echo $year;?></option>
                <?php endforeach;?>
                  </select>
                </td>
              </tr>
              <tr>
                <td class="lightblue" width="15%"><label for="sortcode">Date </label><span id="Addlabelstartdate" class="required">*</span></td>
                <td width="35%">
                  <input type="text" placeholder="dd-mm-yy" name="holidaydate" id="reinitializeCal" class="w-135" >
                </td>
              </tr>
              <tr>
                <td class="lightblue" width="15%">
                  <label for="sortcode">Week</label></td>
                <td width="35%">
                  <span id="showweek"></span>
                  <input type="hidden" name="weekno" id="weekno" value="">
              
                </td>
              </tr>
          
              <tr>
                <td>Description<span id="Addlabelstartdate" class="required">*</span></td>
                <td>
                  <div class="rowsbtns">
                    <div class="fields-color">
                      <input id="description" class="w-135" name="description" value=""  type="text">
                    </div>
                  </div>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <input name="js_holidayid" id="js_holidayid" type="hidden" value="0">
                  <input name="js_actionbutton" id="js_actionbutton" type="hidden" value="create">
                  <input id="js_saveHoliday" name="addteamsubmit" id="addteamsubmit" type="submit" value="Save">
                  <input name="addteamcancel" class="cancel" id="addteamcancel" type="reset" value="Cancel">
                </td>
              </tr>
            </tbody>
          </table>
      </form>
        </div>
        <!--Model content closes-->
      </div>
    </div>
  </div>
  <script>
    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the button that opens the modal
    var btn = document.getElementById("addbtn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("closeupd")[0];

    // When the user clicks the button, open the modal 
    btn.onclick = function () {
      modal.style.display = "block";
    }
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
  </script>

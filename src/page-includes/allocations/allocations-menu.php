<?php

$intID = $_REQUEST['id'];

?>



<ul class="context-menu-list context-menu-root handcursor">
  <li class="context-menu-item icon icon-delete">
    <span>Delete Duty</span>
  </li>
  <li class="context-menu-item icon icon-edit">
  <span onclick='javascript:EditDuty(<?php echo $intID?>)';>Edit Duty</span>
  </li>
  
  <li class="context-menu-separator">  
  </li>
  
  
  <li class="context-menu-item icon icon-comment">
  <span onclick='javascript:EditDuty(<?php echo $intID?>)';>Duty Comments</span>
  </li>
  <li class="context-menu-item icon icon-comment">
  <span onclick='javascript:EditDuty(<?php echo $intID?>)';>Person Comments</span>
  </li>
  <li class="context-menu-separator">  
  </li>  
  <li class="context-menu-item icon icon-misc">
  <span onclick='javascript:EditDuty(<?php echo $intID?>)';>Misc Duty</span>
  </li>
  <li class="context-menu-item icon icon-money">
  <span onclick='javascript:EditAllocatedDuty(<?php echo $intID?>)';>Charging</span>
  </li>
  <li class="context-menu-item icon icon-firstaid">
  <span onclick='javascript:EditSickness(<?php echo $intID?>)';>Sickness</span>
  </li>
  <li class="context-menu-item icon icon-exclaim">
  <span onclick='javascript:EditDuty(<?php echo $intID?>)';>Attention</span>
  </li>
  <li class="context-menu-item icon icon-overtime">
  <span onclick='javascript:EditDuty(<?php echo $intID?>)';>Overtime</span>
  </li>
  <li class="context-menu-item icon icon-leave">
  <span onclick='javascript:EditDuty(<?php echo $intID?>)';>Comp Leave</span>
  </li>
  <li class="context-menu-separator">  
  </li>
  <li class="context-menu-item icon icon-history">
    <span onclick='javascript:ShowDutyHistory(123)';>History</span>
  </li>


</ul>



 



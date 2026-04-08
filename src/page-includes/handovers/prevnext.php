<?php
$date = $_REQUEST['date'];
$yesterday = date("Y-m-d", strtotime("-1 day", (strtotime($date))));
$tomorrow = date("Y-m-d", strtotime("+1 day", (strtotime($date))));

echo '<table border="0">';
  echo '<tr>';
    echo '<td class="handcursor" width="450px" onclick=\'javascript:ListHandovers("'.$yesterday.'")\';>&nbsp;&lt;&lt; '.date("jS M Y", strtotime($yesterday));
    echo '</td>';
    echo '<td class="handcursor" width="450px" onclick=\'javascript:ListHandovers("'.$tomorrow.'")\';>'.date("jS M Y", strtotime($tomorrow)).'&nbsp;&gt;&gt;';
    echo '</td>';
  echo '</tr>';
echo '</table>';

?>
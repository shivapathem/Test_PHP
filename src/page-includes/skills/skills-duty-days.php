<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/skillsfunctions.php';

$currid = $_REQUEST["id"];


die;



echo '<div id="dayddiv"></div>';

?>

<script type="text/javascript">

ShowDays(<?php echo $currid?>);

function ShowDays(id) {
  $.post('page-includes/ajax-calls/skills-fill-days-div.php', {
    id: id
  },
  function(data,status){
  {
    $('#dayddiv').html(data);
  }
  });
  }


  function RemoveDay(day, dutyid) {
    $.post("page-includes/ajax-calls/skills-swapday.php", {
      dutyid: dutyid,
      day: day,
      action: 'remove'
    },
    function(data,status){
      {
        ShowDays(data)
      }
    });
  }

  function AddDay(day, dutyid) {
    $.post("page-includes/ajax-calls/skills-swapday.php", {
      dutyid: dutyid,
      day: day,
      action: 'add'
    },
    function(data,status){
      {
        ShowDays(data)
      }
    });
  }

</script>
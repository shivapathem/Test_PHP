<?php
if(!isset($_POST['width']) || !isset($_POST['height'])) {
echo '
<script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    var height = $(window).height();
    var width = $(window).width();
    $.ajax({
        type: \'POST\',
        url: \'getwidthheight.php\',
        data: {
            "height": height,
            "width": width
        },
        success: function (data) {
            $("body").html(data);
        },
    });
});
</script>
';
}
else {
//echo "Width  : ".$_POST['width']."<br>";
//echo "Height : ".$_POST['height']."<br>";
session_start();
$_SESSION['screen']['screenwidth'] = $_POST['width'];
$_SESSION['screen']['screenheight'] = $_POST['height'];

  header("Location: index.php");

}
?>

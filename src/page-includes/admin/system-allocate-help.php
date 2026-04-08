<?php
include_once '../../function-includes/init.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/genericfunctions.php';
$pdo = OpenDBLinkA7();
$intID = $_REQUEST['id'];


if (isset($_REQUEST['submit'])) {

  $strSubmitMessage = escapeSingleQuotes(htmlspecialchars_decode($_REQUEST['message']));
  $strSubmitDescription = $_REQUEST['description'];
  if ($intID == 0) {
    $strQuery = "INSERT INTO    AllocateDocuments
                              (Description, ContentText)
               VALUES         (?, ?);
               SELECT         SCOPE_IDENTITY() as computed";

    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $strSubmitDescription, PDO::PARAM_STR);
    $stmt->bindParam(2, $strSubmitMessage, PDO::PARAM_STR);
    $result = $stmt->execute();
    $intID  = $pdo->lastInsertId();
  } else {
    $strQuery = "UPDATE     AllocateDocuments
                SET        Description = ?, 
                           ContentText = ?
                WHERE   (ID = ?)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $strSubmitDescription, PDO::PARAM_STR);
    $stmt->bindParam(2, $strSubmitMessage, PDO::PARAM_STR);
    $stmt->bindParam(3, $intID, PDO::PARAM_INT);
    $stmt->execute();
  }
  echo $intID;
} else {
  $arrHelper = GetAllocateHelpDetail($intID);
  if (!empty($arrHelper)) {
    $helperDescription = $arrHelper['Description'];
    $helperContent = $arrHelper['Content'];
    if (strpos($helperContent, 'â€“') !== false) {
      $helperContent = str_replace('â€“', '–', $helperContent);
    }
    if (strpos($helperContent, 'Ã¢â‚¬â€œ') !== false) {
      $helperContent = str_replace('Ã¢â‚¬â€œ', '-', $helperContent);
    }
    if (strpos($helperContent, 'Ã‚') !== false) {
      $helperContent = str_replace('Ã‚', '', $helperContent);
    }

    if (strpos($helperDescription, 'â€“') !== false) {
      $helperDescription = str_replace('â€“', '–', $helperDescription);
    }
    if (strpos($helperDescription, 'Ã¢â‚¬â€œ') !== false) {
      $helperDescription = str_replace('Ã¢â‚¬â€œ', '-', $helperDescription);
    }
    if (strpos($helperDescription, 'Ã‚') !== false) {
      $helperDescription = str_replace('Ã‚', '', $helperDescription);
    }
  } else {
    $helperDescription = '';
    $helperContent = '';
  }

  echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
  echo '<br>Allocate Help <br><br>';
  echo '</div>';

  echo '<br>';

  echo '<form id="allocatehelp">';
  echo '<input type="hidden" name="id" value="' . $intID . '">';
  echo '<table class="tablesmalltidy" width="100%">';
  echo '<tr>';
  echo '<td>';
  echo 'Description (appears on Menu)';
  echo '</td>';
  echo '<td>';
  echo '<input id="Title" id="description" name="description" type="text" size="40" value="' . $helperDescription . '" />';
  echo '</td>';
  echo '</tr>';


  echo '<tr>';
  echo '<td colspan="2">';
  echo '<textarea rows="12" id="message" name="message" cols="60">' . $helperContent . '</textarea>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>';
  echo '<input name="submit" type="submit" value="Update">';
  echo '</td>';
  echo '</tr>';

  echo '</table>';


  echo '</form>';

?>

  <script type="text/javascript">
    $('document').ready(function() {
      $('#allocatehelp').validate({
        rules: {
          "description": {
            required: true,
          }
        },
        submitHandler: function(form) {
          $.ajax({
            type: 'POST',
            url: 'page-includes/admin/system-allocate-help.php',
            data: $('#allocatehelp').serialize(),
            success: function(data) {
              FillMenu();
              ShowAllocateHelp(1)
            }
          });
        }
      })
    });

    $('#message').jqte();
  </script>
  <style>
    .jqte_editor {
      height: 350px;
      max-height: 500px;
    }
  </style>
<?php
}
?>
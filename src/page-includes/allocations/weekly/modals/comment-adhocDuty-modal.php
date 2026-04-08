<?php
session_start();

use Symfony\Component\HttpFoundation\Request;
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
$row = $service->getAdhocDutyByID($request->get("ID"));

$userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$History = 'Adhoc Duty Comments has been edited by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
$historyType = 11;
$row['DutyComment'] = $row['DutyComment'] ? $row['DutyComment'] : '';
$commentArr = explode('__COMMENT_SEPARETOR__', $row['DutyComment']);
?>

<form id="commentAdhocDutyForm">
<table id="adhocdutynewcommenttabledetail" class="tablesmalltidy" width="100%">
        <tbody>
			<tr height="30px"><td style="font-weight: bold; padding: 5px;">Duty Comments</td><td colspan="3">
				<textarea cols="45" rows="5" maxlength="500" name="DutyComment" id = "commentBox"cols="35" placeholder="Please enter Duty Comments"><?php echo isset($commentArr[1]) ? $commentArr[1] : ''; ?></textarea></td>
			</tr>
			<tr height="30px"><td style="font-weight: bold; padding: 5px;">Person Comments</td><td colspan="3">
				<textarea cols="45" rows="5" maxlength="500"  name="Comment" id = "commentBox"cols="35" placeholder="Please enter Person Comments"><?php echo $commentArr[0]; ?></textarea></td>
			</tr>
            <input type="hidden" name="AdhocID" value="<?php echo $row['MasterDutyID']; ?>">
			<input type="hidden" name="userId" value="<?php echo $userID; ?>">
			<input type="hidden" name="message" value="<?php echo $History; ?>">
			<input type="hidden" name="historyType" value="<?php echo $historyType; ?>">
			<input type="hidden" name="attributeId" value="<?php echo $row['MasterDutyID']; ?>">
			<input type="hidden" name="dutyDate" value="<?php echo $request->get('dutyDate'); ?>">
            <input type="hidden" name="action" value="editComment">
        </tbody>
    </table>
    <!-- Allow form submission with keyboard without duplicating the dialog button -->
    <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
</form>
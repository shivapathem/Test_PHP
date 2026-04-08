<?php
$html = '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
</head>
<style>
    body {
        box-sizing: border-box;
        font-size: 15px;
        font-family: inherit;
        font-family: Verdana, Arial, sans-serif;
        font-size: 16px;
    }

    .container {
        max-width: 800px;
        padding: 10px;
        position: relative;
    }

    .fieldset {
        padding: 10px;
        margin-bottom: 5px;
        border: 1px solid #afaeae;
    }

    .charging-container {
        background-color: #eeeeee;
        font-size: 11px;
        padding: 10px;
        margin-bottom: 20px;
    }

    h1.charging-heading {
        background-color: #4b72bf;
        color: #FFF;
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        padding: 5px 0px;
        margin: 0px;
        margin-bottom: 5px;
    }

    .chargeRow {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
    }

    .chargeCol {
        margin-bottom: 4px;
    }

    .chargeCol2 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 16.66667%;
        flex: 0 0 16.66667%;
        max-width: 16.66667%;
    }

    .chargeCol3 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 25%;
        flex: 0 0 25%;
        max-width: 25%;
    }

    .chargeCol4 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 33.33333%;
        flex: 0 0 33.33333%;
        max-width: 33.33333%;
    }

    .chargeCol5 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 41.66667%;
        flex: 0 0 41.66667%;
        max-width: 41.66667%;
    }

    .chargeCol6 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 50%;
        flex: 0 0 50%;
        max-width: 50%;
    }

    .chargeCol7 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 58.3333%;
        flex: 0 0 58.3333%;
        max-width: 58.3333%;
    }

    .chargeCol8 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 66.66667%;
        flex: 0 0 66.66667%;
        max-width: 66.66667%;
    }

    .chargeCol12 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 100%;
        flex: 0 0 100%;
        max-width: 100%;
    }

    .charg-lab {
        display: inline-block;
        text-align: left;
        margin-right: 5px;
        width: 90px;
        font-size: 11px;
    }

    .charg-inp {
        display: inline-block;
        font-size: 11px;
        margin: 5px 0;
        font-weight: bold;
    }

    select.sec {
        display: inline-block;
        font-size: 11px;
    }


    .fieldset-legend {
        font-size: 12px;
        font-weight: bold;
    }

    .footer{
        font-size: 11px;
    }

    .footer img {
        margin-top: 10px;
        margin-bottom: 2px;
        cursor: pointer;
        min-width: auto;
        min-height: auto;
        max-width: 100%;
        height: 16px;
    }

    .charging-dec {
        font-size: 11px;
        line-height: 1.5;
    }

    .fieldsetBottomTitle {
        font-size: 12px;
        font-weight: bold;
        margin: 0;
        margin-bottom: 5px;
    }
</style>

<body>
    <div class="container">
        <p class="charging-dec">
            Charging on <b>'.date('d/m/Y', strtotime($chargingResult1['ChargingDutyDate'])).'</b> has been deleted because a Shiftleader swapped or unassigned a duty.
            The charging will need to be re-entered from scratch, and the original details are listed below to help you:
        </p>

        <div class="charging-container">
            <div class="fieldsetTopWrapper">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Name & Duty Details</legend>
                    <div class="chargeRow">
                        <div class="chargeCol chargeCol4">
                            <label class="charg-lab">First Name </label>
                            <p class="charg-inp">'.$chargingResult1['DisplayFirstname'].'</p>
                        </div>
                        <div class="chargeCol chargeCol4">
                            <label class="charg-lab">Last Name</label>
                            <p class="charg-inp">'.$chargingResult1['DisplayLastname'].'</p>
                        </div>
                        <div class="chargeCol chargeCol4">
                            <label class="charg-lab">Date</label>
                            <p class="charg-inp">'.date('d/m/Y', strtotime($chargingResult1['ChargingDutyDate'])).'</p>
                        </div>
                        <div class="chargeCol chargeCol4">
                            <label class="charg-lab">Duty</label>
                            <p class="charg-inp">'.$chargingResult1['DutyName'].'</p>
                        </div>
                        <div class="chargeCol chargeCol4">
                            <label class="charg-lab">Charge Code</label>
                            <p class="charg-inp">'.$chargingResult1['EstablishCode'].'</p>
                        </div>
                        <div class="chargeCol chargeCol4">
                            <label class="charg-lab">Description</label>
                            <p class="charg-inp">'.$chargingResult1['EstablishCodeDescription'].'</p>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>';
		$recordNumber = 1;
	foreach($chargingResult as $chargingResultVal)
	{
		if($chargingResultVal['IsActual'] == 0)
		{
			$status = 'Provisional';
		}elseif($chargingResultVal['IsActual'] == 1)
		{
			$status = 'Actual';
		}else
		{
			$status = 'Hold';
		}
        $html .='<h2 class="fieldsetBottomTitle">Record '.$recordNumber.'</h2>
        <div class="charging-container">
            <div class="fieldsetBottomWrapper">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Activity Details</legend>
                    <div class="chargeRow">
                        <div class="chargeCol chargeCol4">
                            <label class="charg-lab">Activity Code</label>
                            <p class="charg-inp">'.$chargingResultVal['ActivityCodeName'].'</p>
                        </div>
                        <div class="chargeCol chargeCol8">
                            <label class="charg-lab">Description</label>
                            <p class="charg-inp">'.$chargingResultVal['Description'].'</p>
                        </div>
                        <div class="chargeCol chargeCol4">
                            <label class="charg-lab">Unit Price</label>
                            <p class="charg-inp"><span>&pound; </span>'.$chargingResultVal['UnitPrice'].'</p>
                        </div>
                        <div class="chargeCol chargeCol3">
                            <label class="charg-lab">Status</label>
                            <p class="charg-inp">'.$status.'</p>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Charge From</legend>
                    <div class="chargeRow">
                        <div class="chargeCol chargeCol12">
                            <p class="charg-inp">'.$chargingResultVal['ChargeWbsCodeName'].'</p>
                        </div>
                        <div class="chargeCol chargeCol4">
                            <label class="charg-lab">Charge Code</label>
                            <p class="charg-inp">'.$chargingResultVal['EstablishCode'].'</p>
                        </div>
                        <div class="chargeCol chargeCol8">
                            <label class="charg-lab">Description</label>
                            <p class="charg-inp">
                                '.$chargingResultVal['EstablishCodeDescription'].'
                            </p>
                        </div>
                        <div class="chargeCol chargeCol12">
                            <label class="charg-lab">Comments</label>
                            <p class="charg-inp">'.$chargingResultVal['Comments'].'</p>
                        </div>
                        <div class="chargeCol chargeCol3">
                            <label class="charg-lab">Contact</label>
                            <p class="charg-inp">'.$chargingResultVal['Contact'].'</p>
                        </div>
                        <div class="chargeCol chargeCol3">
                            <label class="charg-lab">Telephone</label>
                            <p class="charg-inp">'.$chargingResultVal['Telephone'].'</p>
                        </div>
                        <div class="chargeCol chargeCol3">
                            <label class="charg-lab">Quantity</label>
                            <p class="charg-inp">'.$chargingResultVal['Quantity'].'</p>
                        </div>
                        <div class="chargeCol chargeCol3">
                            <label class="charg-lab">Total Price</label>
                            <p class="charg-inp"><span>&pound; </span>'.($chargingResultVal['UnitPrice'] * $chargingResultVal['Quantity']).'</p>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>';
		$recordNumber++;
	}
        $html .= '<div class="footer" align="center">
            <img src="cid:bbclogo" alt="BBC">
            <br>
            <span>
                <font size="1">BBC '.romanNumerals(date("Y")).'<font>
            </span>
        </div>
    </div>
</body>

</html>';


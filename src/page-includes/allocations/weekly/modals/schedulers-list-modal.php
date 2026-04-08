<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

$request = Request::createFromGlobals();
$service = new AllocationService();

$getSchedulingTeamDetails = $service->getSchedulersList($request);
$schedulers = [];
$srschedulers = [];
if(!empty($getSchedulingTeamDetails)){
    foreach($getSchedulingTeamDetails as $key => $value){
        switch(strtoupper($value['RoleName'])){
            case "SCHEDULER":
                $schedulers[] = $value['DisplayName'];
                break;
            case "SENIOR SCHEDULER":
                $srschedulers[] = $value['DisplayName'];
                break;
        }
    }
}
?>
<style type="text/css">
.main-div{
    width:100%;
    max-height: 300px;
    overflow-y: scroll;
}

.heading-div-srscheduler{
    border: 1px solid #cdcdcd;
    padding:4px;
    font-size: 12px;
    font-weight:bold;
}

.heading-div-scheduler{
    border-top: 1px solid #cdcdcd;
    border-right: 1px solid #cdcdcd;
    border-bottom: 1px solid #cdcdcd;
    padding:4px;
    font-size: 12px;
    font-weight:bold;
}

.main-content-div{
    display:inline-block;
    text-align:center;
    vertical-align:middle;
    width:50%;
    float: left;
}

.content-srscheduler{
    display:inline-block;
    text-align:center;
    vertical-align:middle;
    width:100%; float: left;
    border-left: 1px solid #cdcdcd;
    border-bottom: 1px solid #cdcdcd;
    border-right: 1px solid #cdcdcd;
    padding:4px;
    font-size: 12px;
}

.content-scheduler{
    display:inline-block;
    text-align:center;
    vertical-align:middle;
    width:100%;
    float: left;
    border-left: 1px solid #cdcdcd;
    border-bottom: 1px solid #cdcdcd;
    border-right: 1px solid #cdcdcd;
    padding:4px;
    font-size: 12px;
}
.fixed-header{
    position:absolute;
    width:92%;
    background-color: #FFFFFF;
}
</style>
<div class="main-div">
    <div class="fixed-header">
        <div class="main-content-div heading-div-srscheduler">Senior Schedulers</div>
        <div class="main-content-div heading-div-scheduler">Schedulers</div>
    </div>
    <div style="margin-top: 7%;">
        <div class="main-content-div">
            <?php if(empty($srschedulers)){?>
                <div class="content-srscheduler">No Senior Schedulers</div>
            <?php } else {?>
                <?php foreach($srschedulers as $srSchName){?>
                    <div class="content-srscheduler"><?php echo $srSchName;?></div>
                <?php }?>
            <?php }?>
        </div>
        <div class="main-content-div">
            <?php if(empty($schedulers)){?>
                <div class="content-scheduler">No Schedulers</div>
            <?php } else {?>
                <?php foreach($schedulers as $schName){?>
                    <div class="content-scheduler"><?php echo $schName;?></div>
                <?php }?>
            <?php }?>
        </div>
    </div>
</div>
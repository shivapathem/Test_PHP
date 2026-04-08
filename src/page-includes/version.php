<?php

require_once('../function-includes/init.php');

    $path = getenv('ALLOCATE_OCTOPUS_PATH')."/DeploymentJournal.xml";
    $path = str_replace("'","",$path);
    $packageVersion = '--';
    if (file_exists($path)) {
        $xmlvalue = (array) simplexml_load_file($path) ? (array) simplexml_load_file($path) : '';
        if (is_array($xmlvalue)) {
            $latestDate = null;
            $latestVersion = '--';
            foreach ($xmlvalue['Deployment'] as $deployment) {
                $deployment = (array) $deployment;
                $package = (array) $deployment['Package'];
                if (strpos(strtoupper($package['@attributes']['PackageId']), 'ALLOCATE7') !== false) {
                    $installedOn = strtotime($deployment['@attributes']['InstalledOn']);
                    if ($latestDate === null || $installedOn > $latestDate) {
                        $latestDate = $installedOn;
                        $latestVersion = $package['@attributes']['PackageVersion'];
                    }
                }
            }
            $packageVersion = $latestVersion;
        }
    }
    echo '<table class="tablesmalltidy" width="600px">';
    echo '<tr>';
    echo '<th><br>This version of the Website is<br>' .$packageVersion. '<br><br></th>'; 
    echo '</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="2" align="center"><input type="button" value="OK" onclick="cancel()"></td>';
    echo '</tr>';
    echo '</table>';
?>

<script type="text/javascript">
function cancel() {
    $.facebox.close();
}
</script>

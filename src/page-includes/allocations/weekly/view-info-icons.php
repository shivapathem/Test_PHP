<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
?>
<div class="popupHeading">Key...</div>
    <form>
        <table class="tablesmalltidy" width="100%">
            <tr>
                <td>Volunteered for Overtime </td>
                <td><div class="viewIconstext view-blue-pound-icon"></div></td>
            </tr>
            <tr>
                <td>Marked Overtime </td>
                <td><div class="viewmarkovertime"><div></td>
            </tr>
            <tr>
                <td>Person Comments </td>
                <td><div id="circle"  class="golden viewIconsAlign"></div></td>
            </tr>
            <tr>
                <td>Duty Comments  </td>
                <td><div id="circle"  class="blue viewIconsAlign"></td>
            </tr>
            <tr>
                <td>Person & Duty Comments</td>
                <td><div id="circle" class="pink viewIconsAlign"></div></td>
            </tr>
            <tr>
                <td>Charging Provisional </td>
                <td><div class="contextCornerIcon cornerIcon cornerIcon-Red"></div></td>
            </tr>
            <tr>
                <td>Charging Actual </td>
                <td><div class="contextCornerIcon cornerIcon cornerIcon-Green"></div></td>
            </tr>
            <tr>
                <td>Charging Actual and Provisional </td>
                <td><div class="contextCornerIcon cornerIcon cornerIcon-Blue"></div></td>
            </tr>
            <tr>
                <td>Authorised Over 12</td>
                <td><div class="viewIconstext"><b>12</b></div></td>
            </tr>
            <tr>
                <td>Overridden Under11</td>
                <td><div class="viewIconstext"><b>11</b></div></td>
            </tr>
            <tr>
                <td>Mark as Actual</td>
                <td><div class="viewIconstext"><b>A</b></div></td>
            </tr>
            <tr>
                <td>Mark as WIAD</td>
                <td><div class="viewIconstext"><b>W</b></div></td>
            </tr>
        </table>
    </form>
</div>



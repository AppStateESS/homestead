<style type="text/css">
td.floor {
    background-color: blue;
    color: white;
}

td.room {
    background-color: green;
    color: white;
}

.toggle1 {
    background-color: #AAAAAA;
}

.toggle2{
    background-color: #CCCCCC;
}
</style>

<style type="text/css" media="print">
</style>

<h2>Building Overview for {HALL}</h2>
<table>
<!-- BEGIN floor_repeat -->
        <tr>
            <td class="floor" colspan="6">Floor {FLOOR_NUMBER}</th>
        </tr>
    <!-- BEGIN room_repeat -->
        <tr>
            <td>&nbsp;</td>
            <td class="room" colspan="5">Room {ROOM_NUMBER}</td>
        </tr>
        <!-- BEGIN bed_repeat -->
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="{TOGGLE}">{BED_LABEL} : {BED}</td>
            <td class="{TOGGLE}">{BANNER_ID}</td>
            <td class="{TOGGLE}">{USERNAME}</td>
            <td class="{TOGGLE}">{NAME}</td>
        </tr>
        <!-- END bed_repeat -->
    <!-- END room_repeat -->
<!-- END floor_repeat -->
</table>

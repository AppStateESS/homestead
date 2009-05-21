<style type="text/css">
td.floor {
    background-color: #3B5998;
    color: white;
}

td.room {
    background-color: #8497BF;
    color: white;
}

.toggle1 {
    background-color: #ECEFF5;
}

.toggle2 {
    background-color: #DDDDDD;
}

.vacant {
    background-color: #FFFFCC;
}

a.username {
    color: #43609C;
}

.overview_table {
    padding: 3px;
    border-width: 2px;
    border-color: #FFFFFF;
    border-collapse: separate;
}

a.hall_link {
    color: white;
}
</style>

<style type="text/css" media="print">
</style>

<h2>Building Overview for {HALL}</h2>
<table class="overview_table" width="400">
<!-- BEGIN floor_repeat -->
        <tr>
            <td class="floor" colspan="7">{FLOOR_NUMBER} {FLOOR_RLC}</th>
        </tr>
    <!-- BEGIN room_repeat -->
        <tr>
            <td>&nbsp;</td>
            <td class="room" colspan="6">{ROOM_NUMBER} {EXTRA_ATTRIBS}</td>
        </tr>
        <!-- BEGIN bed_repeat -->
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="{TOGGLE}">{BED_LABEL} : {BED}</td>
            <td class="{TOGGLE}">{BANNER_ID}</td>
            <td class="{TOGGLE}">{USERNAME}</td>
            <td class="{TOGGLE}">{RLC_ABBR}</td>
            <td class="{TOGGLE}" align="right">{NAME}</td>
        </tr>
        <!-- END bed_repeat -->
    <!-- END room_repeat -->
<!-- END floor_repeat -->
</table>

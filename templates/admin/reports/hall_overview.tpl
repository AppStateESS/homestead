<style type="text/css">
#hall_overview td.floor {
    background-color: #3B5998;
    color: white;
}

#hall_overview td.floor a{
    color: white;
}

#hall_overview td.room {
    background-color: #8497BF;
    color: white;
}

#hall_overview td.room a {
    color: white;
}

#hall_overview a.hall_link {
    color: white;
}
</style>

<h1>Building Overview for {HALL} - {TERM}</h1>
<div class="row">
    <div class="col-md-7">

        <table id="hall_overview" class="table table-striped table-hover">
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
                    <td>{BED_LABEL} : {BED}</td>
                    <td>{BANNER_ID}</td>
                    <td>{USERNAME}</td>
                    <td>{RLC_ABBR}</td>
                    <td class="text-right">
                        <!-- BEGIN vacant -->
                        {VACANT}
                        <span class="label label-warning">Vacant</span>
                        <!-- END vacant -->
                        {NAME}
                    </td>
                </tr>
                <!-- END bed_repeat -->
                <!-- END room_repeat -->
                <!-- END floor_repeat -->
            </table>
        </div>
    </div>

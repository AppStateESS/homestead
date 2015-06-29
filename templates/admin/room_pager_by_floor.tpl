<table class="table table-striped table-hover">
    <tr>
        <th>Room Number</th>
        <th>Gender Type</th>
        <th>Default Gender</th>
        <th>RLC Reservation</th>
        <th>RA</th>
        <th>Private</th>
        <th>Overflow</th>
        <th>ADA</th>
        <th>Reserved</th>
        <th>Offline</th>
        <th>&nbsp;</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="10">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr name="row" id={ID} {FORM} {TOGGLE}>
        {START_FORM}
        <td id="{ID}room_number">{ROOM_NUMBER}</td>
        <td id="{ID}gender_type">{GENDER_TYPE}</td>
        <td id="{ID}default_gender">{DEFAULT_GENDER}</td>
        <td id="{ID}rlc_reserved">{RLC_RESERVED}</td>
        <td id="{ID}ra_room">{RA}</td>
        <td id="{ID}private_room">{PRIVATE}</td>
        <td id="{ID}is_overflow">{OVERFLOW}</td>
        <td id="{ID}is_medical">{ADA}</td>
        <td id="{ID}is_reserved">{RESERVED}</td>
        <td id="{ID}is_online">{OFFLINE}</td>
        <td>{DELETE}</td>
        {END_FORM}
    </tr>
    <!-- END listrows -->
</table>
<a href="{ADD_ROOM_URI}" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> Add Room</a>
<!-- BEGIN page_label -->
<p class="text-center">
Rooms {TOTAL_ROWS}
</p>
<!-- END page_label -->

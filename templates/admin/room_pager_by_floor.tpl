<h2>{TABLE_TITLE}</h2>
<table width="%70">
    <tr>
        <th>{ROOM_NUM_LABEL}</th>
        <th>{GENDER_TYPE_LABEL}</th>
        <th>{RA_LABEL}</th>
        <th>{PRIVATE_LABEL}</th>
        <th>{OVERFLOW_LABEL}</th>
        <th>{MEDICAL_LABEL}</th>
        <th>{RESERVED_LABEL}</th>
        <th>{ONLINE_LABEL}</th>
        <th>{DELETE_LABEL}</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="2">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr name="row" id={ID} {FORM} {TOGGLE}>
        <td id={ID} {FORM} name="room_number">{START_FORM}{ROOM_NUMBER}</td>
        <td id={ID} {FORM} name="gender_type">{GENDER_TYPE}</td>
        <td id={ID} {FORM} name="ra_room">{RA_ROOM}</td>
        <td id={ID} {FORM} name="private_room">{PRIVATE_ROOM}</td>
        <td id={ID} {FORM} name="is_overflow">{IS_OVERFLOW}</td>
        <td id={ID} {FORM} name="is_medical">{IS_MEDICAL}</td>
        <td id={ID} {FORM} name="is_reserved">{IS_RESERVED}</td>
        <td id={ID} {FORM} name="is_online">{IS_ONLINE}{END_FORM}</td>
    </tr>
    <!-- END listrows -->
</table>
<br />
<!-- BEGIN page_label -->
<div align="center">
Rooms: {TOTAL_ROWS}
</div>
<!-- END page_label -->
<!-- BEGIN pages -->
<div align="center">
{PAGE_LABEL}: {PAGES}
</div>
<!-- END pages -->
<!-- BEGIN limits -->
<div align="center">
{LIMIT_LABEL}: {LIMITS}
</div>
<!-- END limits -->

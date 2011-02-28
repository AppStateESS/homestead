<h2>{TABLE_TITLE}</h2>
<table width="%70">
    <tr>
        <th>{ROOM_NUM_LABEL}</th>
        <th>{GENDER_TYPE_LABEL}</th>
        <th>{DEFAULT_GENDER_LABEL}</th>
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
        <td id="{ID}">{START_FORM}{ROOM_NUMBER}</td>
        <td id="{ID}">{GENDER_TYPE}</td>
        <td id="{ID}">{DEFAULT_GENDER}</td>
        <td id="{ID}">{RA_ROOM}</td>
        <td id="{ID}">{PRIVATE_ROOM}</td>
        <td id="{ID}">{IS_OVERFLOW}</td>
        <td id="{ID}">{IS_MEDICAL}</td>
        <td id="{ID}">{IS_RESERVED}</td>
        <td id="{ID}">{IS_ONLINE}{END_FORM}</td>
        <td>{DELETE}</td>
    </tr>
    <!-- END listrows -->
</table>
{ADD_ROOM_LINK}
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

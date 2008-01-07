<h2>{TABLE_TITLE}</h2>
<table width="%70">
    <tr>
        <th>{ROOM_NUM_LABEL}</th>
        <th>{GENDER_TYPE_LABEL}</th>
        <th>{RA_LABEL}</th>
        <th>{PRIVATE_LABEL}</th>
        <th>{LOBBY_LABEL}</th>
        <th>{MEDICAL_LABEL}</th>
        <th>{RESERVED_LABEL}</th>
        <th>{ONLINE_LABEL}</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="2">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{ROOM_NUMBER}</td>
        <td>{GENDER_TYPE}</td>
        <td>{RA_ROOM}</td>
        <td>{PRIVATE_ROOM}</td>
        <td>{IS_LOBBY}</td>
        <td>{IS_MEDICAL}</td>
        <td>{IS_RESERVED}</td>
        <td>{IS_ONLINE}</td>
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

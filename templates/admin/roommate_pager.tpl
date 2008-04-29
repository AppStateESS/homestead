<h2>{TABLE_TITLE}</h2>
<table width="%90">
    <tr>
        <th>Requestor</th>
        <th>Requestee</th>
        <th>Requested On</th>
        <th>Confirmed on</th>
        <th>Action</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="2">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{REQUESTOR}</td>
        <td>{REQUESTEE}</td>
        <td>{REQUESTED_ON}</td>
        <td>{CONFIRMED_ON}</td>
        <td>{ACTION}</td>
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
<br />
{SEARCH}

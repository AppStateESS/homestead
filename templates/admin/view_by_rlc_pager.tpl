{BACK_LINK}
<h1>{TITLE}</h1>
<table>
    <tr>
        <th>Name</th>
        <th>Banner Id</th>
        <th>Gender</th>
        <th>User name</th>
        <th>Assignment</th>
        <th>Roommate</th>
        <th>Action</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="4">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{NAME}</td>
        <td>{BANNER_ID}</td>
        <td style="text-align:center;">{GENDER}</td>
        <td>{USERNAME}</td>
        <td>{ROOM_ASSIGN}</td>
        <td>{ROOMMATES}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
<br />
<!-- BEGIN page_label -->
<div align="center">
Assignments: {TOTAL_ROWS}
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
<!-- BEGIN csv -->
{CSV_REPORT}
<!-- END csv -->

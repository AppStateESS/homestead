<h2>{TITLE}</h2>
<table width="%60">
    <tr>
        <th>Name</th>
        <th>Gender</th>
        <th>User name</th>
        <th>Action</th>
        <th>Roommates</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="4">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{NAME}</td>
        <td>{GENDER}</td>
        <td>{USERNAME}</td>
        <td>{ACTION}</td>
        <td>{ROOMMATES}</td>
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

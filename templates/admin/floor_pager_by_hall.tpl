<h2>{TABLE_TITLE}</h2>
<table width="%70">
    <tr>
        <th>{FLOOR_NUM_LABEL}</th>
        <th>{GENDER_LABEL}</th>
        <th>{ONLINE_LABEL}</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="2">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{FLOOR_NUMBER}</td>
        <td>{GENDER_TYPE}</td>
        <td>{IS_ONLINE}</td>
    </tr>
    <!-- END listrows -->
</table>
<br />
<!-- BEGIN page_label -->
<div align="center">
Floors: {TOTAL_ROWS}
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

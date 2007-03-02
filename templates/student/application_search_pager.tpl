<table cellpadding="4" cellspacing="1" width="100%">
    <tr>
        <th>{USERNAME}</th>
        <th>{FIRST_NAME}</th>
        <th>{LAST_NAME}</th>
        <th>{ACTIONS}</th>
    </tr>

    <!-- BEGIN empty_table -->
    <td colspan="4">
        {EMPTY_MESSAGE}
    </td>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{STUDENT_ID}</td>
        <td>{FIRST_NAME}</td>
        <td>{LAST_NAME}</td>
        <td>{ACTIONS}</td>
    </tr>
    <!-- END listrows -->
</table>

<!-- BEGIN page_label -->
<div align="center">
    Results: {TOTAL_ROWS}
</div>
<!-- END page_label -->

<!-- BEGIN pages -->
<div align="center">
    {PAGE_LABEL: {PAGERS}
</div>
<!-- END pages -->

<!-- BEGIN limits -->
<div align="center">
    {LIMIT_LABEL}: {LIMITS}
</div>
<!-- END limits -->

<table width="100%">
    <tr>
        <th>Category</th>
        <th>Description</th>
        <th>Term</th>
        <th>Reported On</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="2">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{CATEGORY}</td>
        <td>{DESCRIPTION}</td>
        <td>{TERM}</td>
        <td>{REPORTED_ON}</td>
    </tr>
    <!-- END listrows -->
</table>
<!-- BEGIN page_label -->
<div align="center">
Total damages: {TOTAL_ROWS}
</div>
<!-- END page_label -->
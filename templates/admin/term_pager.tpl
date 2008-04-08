<!-- BEGIN table -->
<table cellpadding="4" cellspacing="1" width="99%">
    <tr>
        <th>Term</th>
        <th>Banner Queue</th>
        <th>Action</th>
    </tr>
<!-- BEGIN empty_table -->
    <tr>
        <td colspan="2">
            <p>{EMPTY_MESSAGE}</p>
        </td>
    </tr>
<!-- END empty_table -->
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{TERM}</td>
        <td>{BANNER_QUEUE}</td>
        <td>{ACTION}</td>
    </tr>
<!-- END listrows -->
</table>
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}
</div>
<!-- END table -->

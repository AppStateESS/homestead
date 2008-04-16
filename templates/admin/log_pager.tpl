<!-- BEGIN table -->
<table cellpadding="4" cellspacing="1" width="99%">
    <tr>
        <th>Users</th>
        <th>Activity</th>
        <th>Time</th>
        <th>Notes</th>
    </tr>
<!-- BEGIN empty_table -->
    <tr>
        <td colspan="5">
            <p>{EMPTY_MESSAGE}</p>
        </td>
    </tr>
<!-- END empty_table -->
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{ACTEE}<br /><strong>By:</strong> {ACTOR}</td>
        <td style="font-weight: bold;">{ACTIVITY}</td>
        <td>{DATE}<br />{TIME}</td>
        <td>{NOTES}</td>
    </tr>
<!-- END listrows -->
</table>
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}
</div>
<!-- END table -->

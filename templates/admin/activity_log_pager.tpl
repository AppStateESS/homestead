<!-- BEGIN table -->
<table cellpadding="4" cellspacing="1" width="99%">
    <tr>
        <th>User {USER_ID_SORT}</th>
        <th>Activity</th>
        <th>Time {TIMESTAMP_SORT}</th>
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
        <td>{ACTEE} <!-- BEGIN by -->(<strong>By:</strong> {ACTOR})<!-- END by --></td>
        <td style="font-weight: bold;">{ACTIVITY}</td>
        <td>{DATE} at {TIME}</td>
    </tr>
<!-- BEGIN notes -->
    <tr {TOGGLE}>
        <td colspan="3"><strong>Notes:</strong> {NOTES}</td>
    </tr>
<!-- END notes -->
<!-- END listrows -->
</table>
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}
</div>
<!-- END table -->

<!-- BEGIN table -->
<table cellpadding="4" cellspacing="1" width="99%">
    <tr>
        <th>User {USER_ID_SORT}</th>
        <th>Activity</th>
        <th>Time {TIMESTAMP_SORT}</th>
        <th>Notes {NOTES_SORT}</th>
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
        <td>{ACTEE}<br /><strong><!-- BEGIN by -->By:</strong> {ACTOR}</td><!-- END by -->
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

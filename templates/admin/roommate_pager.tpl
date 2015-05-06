<h2>{TABLE_TITLE}</h2>
<table class="table table-striped">
    <tr>
        <th>Requestor</th>
        <th>Requestee</th>
        <th>Requested On</th>
        <th>Confirmed on</th>
        <th>Action</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="5">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr>
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
<div class="text-center">
Requests: {TOTAL_ROWS}
</div>
<!-- END page_label -->
<!-- BEGIN pages -->
<div class="text-center">
{PAGE_LABEL}: {PAGES}
</div>
<!-- END pages -->
<!-- BEGIN limits -->
<div class="text-center">
{LIMIT_LABEL}: {LIMITS}
</div>
<!-- END limits -->
<br />
{SEARCH}

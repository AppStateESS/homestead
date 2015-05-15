<h2>{TABLE_TITLE}</h2>
<table class="table table-striped table-hover">
    <tr>
        <th>{BEGIN_TIMESTAMP_LABEL}</th>
        <th>{END_TIMESTAMP_LABEL}</th>
        <th>{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="3">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr>
        <td>{BEGIN_TIMESTAMP}</td>
        <td>{END_TIMESTAMP}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>

<br />

<!-- BEGIN page_label -->
<div class="text-center">
Times: {TOTAL_ROWS}
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

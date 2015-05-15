<h2>{TITLE}</h2>

<!-- BEGIN options -->
<ul>
    <li>{PRINT_RECORDS}</li>
    <li>{EXPORT}</li>
</ul>
<!-- END options -->

<table class="table table-striped">
    <tr>
        <th>Name {USER_ID_SORT}</th>
        <th>Final RLC {RLC_ID_SORT}</th>
        <th>Address</th>
        <th>Phone/Email</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td>{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr>
        <td>{NAME}</td>
        <td>{FINAL_RLC}</td>
        <td>{ADDRESS}</td>
        <td>{PHONE}<br />{EMAIL}</td>
    </tr>
    <!-- END listrows -->
</table>
<div class="text-center">
    <p>{TOTAL_ROWS}</p>
    {PAGES}
    <p>{LIMIT_LABEL} {LIMITS}</p>
    {CSV_REPORT}
</div>

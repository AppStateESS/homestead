<table id="special-interest-table" class="table table-striped">
    <tr>
        <th>Name {ASU_USERNAME_SORT}</th>
        <th>User Name</th>
        <th>Banner ID</th>
        <th>Action</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="4">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table-->
    <!-- BEGIN listrows -->
    <tr class="{ROW_CLASS}">
        <td>{NAME}</td>
        <td>{USER}</td>
        <td>{BANNER_ID}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
<div class="text-center">
     {PAGES}
     <p>{TOTAL_ROWS}</p>
     <p>{LIMIT_LABEL} {LIMITS}</p>
    {CSV_REPORT}
</div>

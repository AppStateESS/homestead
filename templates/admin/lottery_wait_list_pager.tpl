<h2>{TITLE}</h2>

<div class="float-right form-group">{SEARCH}</div>
<table class="table table-striped table-hover">
    <tr>
        <th>Position</th>
        <th>Name</th>
        <th>User Name</th>
        <th>Banner ID</th>
        <th>Class</th>
        <th>Gender</th>
        <th>Date</th>
        <th>Cell Phone</th>
        <th>Action</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="9">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr>
        <td>{POSITION}</td>
        <td>{NAME}</td>
        <td>{USER}</td>
        <td>{BANNER_ID}</td>
        <td>{CLASS}</td>
        <td>{GENDER}</td>
        <td>{APP_DATE}</td>
        <td>{PHONE}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
<div class="text-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}<br />
    {CSV_REPORT}
</div>

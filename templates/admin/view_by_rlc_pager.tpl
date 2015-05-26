<a href="{BACK_LINK}" class="btn btn-info"><i class="fa fa-arrow-left"></i> RLC List</a>
<h2>{TITLE}</h2>

<p><a href="{ADD_URI}" class="btn btn-success"><i class="fa fa-plus"></i> Add Member(s)</a></p>

<table class="table table-striped">
    <tr>
        <th>Name</th>
        <th>Banner Id</th>
        <th>Gender</th>
        <th>Student Type</th>
        <th>User name</th>
        <th>Status</th>
        <th>Assignment</th>
        <th>Roommate</th>
        <th>Action</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="4">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr>
        <td>{NAME}</td>
        <td>{BANNER_ID}</td>
        <td style="text-align:center;">{GENDER}</td>
        <td>{STUDENT_TYPE}</td>
        <td>{USERNAME}</td>
        <td>{STATE}</td>
        <td>{ROOM_ASSIGN}</td>
        <td>{ROOMMATES}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
<!-- BEGIN page_label -->
<div align="center">
Assignments: {TOTAL_ROWS}
</div>
<!-- END page_label -->
<div class="text-center">
{PAGES}
<p>{LIMIT_LABEL}: {LIMITS}</p>
{CSV_REPORT}
</div>

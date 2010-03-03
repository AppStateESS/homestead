<h2>{TITLE}</h2>

<div align="right">{SEARCH}</div>
<table>
    <tr>
        <th>Name {ASU_USERNAME_SORT}</th>
        <th>User Name</th>
        <th>Banner ID</th>
        <th>Class{APPLICATION_TERM_SORT}</th>
        <th>Gender{GENDER_SORT}</th>
        <th>Cell Phone</th>
        <th>Action</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="5">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr>
        <td>{NAME}</td>
        <td>{USER}</td>
        <td>{BANNER_ID}</td>
        <td>{CLASS}</td>
        <td>{GENDER}</td>
        <td>{PHONE}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}<br />
    {CSV_REPORT}
</div>

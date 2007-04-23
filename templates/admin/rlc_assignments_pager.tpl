{START_FORM}
<!-- BEGIN empty_table -->
        <p>{EMPTY_MESSAGE}</p>
<!-- END empty_table -->
<!-- BEGIN table -->
<table cellpadding="4" cellspacing="1" width="99%">
    <tr>
        <th>Name</th>
        <th>1st Choice</th>
        <th>Final RLC</th>
        <th>2nd Choice</th>
        <th>3rd Choice</th>
        <th>Special Pop</th>
        <th>Major</th>
        <th>HS GPA</th>
        <th>Gender</th>
        <th>Apply Date</th>
        <th>Course OK?</th>
    </tr>
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{NAME}</td>
        <td>{1ST_CHOICE}</td>
        <td>{FINAL_RLC}</td>
        <td>{2ND_CHOICE}</td>
        <td>{3RD_CHOICE}</td>
        <td>{SPECIAL_POP}</td>
        <td>{MAJOR}</td>
        <td>{HS_GPA}</td>
        <td>{GENDER}</td>
        <td>{APPLY_DATE}</td>
        <td>{COURSE_OK}</td>
    </tr>
<!-- END listrows -->
</table>
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}
</div>
<!-- END table -->
{SUBMIT}
{END_FORM}

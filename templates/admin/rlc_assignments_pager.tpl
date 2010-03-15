{DROPDOWN}
{START_FORM}
<!-- BEGIN table -->
<table cellpadding="3" cellspacing="1" width="850px">
    <tr>
        <th>Name </th>
        <th>1st {RLC_FIRST_CHOICE_ID_SORT}</th>
        <th>2nd {RLC_SECOND_CHOICE_ID_SORT}</th>
        <th>3rd {RLC_THIRD_CHOICE_ID_SORT}</th>
        <th>Sex</th>
        <th>Date {DATE_SUBMITTED_SORT}</th>
        <th>Final RLC</th>
        <th>Action</th>
    </tr>
<!-- BEGIN empty_table -->
    <tr>
        <td colspan="11">
            <p>{EMPTY_MESSAGE}</p>
        </td>
    </tr>
<!-- END empty_table -->
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{NAME}</td>
        <td>{1ST_CHOICE}</td>
        <td>{2ND_CHOICE}</td>
        <td>{3RD_CHOICE}</td>
        <td width="20px">{GENDER}</td>
        <td width="75px">{DATE_SUBMITTED}</td>
        <td>{FINAL_RLC}</td>
        <td>{DENY}</td>
    </tr>
<!-- END listrows -->
</table>
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}<br />
    {CSV_REPORT}
</div>
<!-- END table -->
{SUBMIT}
{END_FORM}

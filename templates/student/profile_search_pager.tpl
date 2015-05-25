

<table class="table table-striped">
    <tr>
        <th>{FIRST_NAME}</th>
        <th>{LAST_NAME}</th>
        <th>{USERNAME}</th>
        <th>{ACTIONS}</th>
    </tr>

    <!-- BEGIN empty_table -->
    <td colspan="11">
        {EMPTY_MESSAGE}
    </td>
    <!-- END empty_table -->
    <!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{FIRST_NAME}</td>
        <td>{LAST_NAME}</td>
        <td>{STUDENT_ID}</td>
        <td>{ACTIONS}</td>
    </tr>
    <!-- END listrows -->
</table>

<div class="text-center">
  {PAGES}
  <p>{TOTAL_ROWS}</p>
  <p>{LIMIT_LABEL} {LIMITS}</p>
  {CSV_REPORT}
</div>

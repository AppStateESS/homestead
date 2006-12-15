<table cellpadding="6" cellspacing="1" width="100%">
  <tr>
    <th>LAST NAME</th>
    <th>FIRST NAME</th>
    <th>EMAIL ADDRESS</th>
    <th>ASU USERNAME</th>
    <th>ACTIONS</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{LAST_NAME}</td>
    <td>{FIRST_NAME}</td>
    <td>{EMAIL_ADDRESS}</td>
    <td>{ASU_USERNAME}</td>   
    <td>{ACTIONS}</td>
  </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>

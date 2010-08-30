<h2>Room Change Requests</h2>

<table>
<tr>
  <th>Student</th>
  <th>User name</th>
  <th>Status</th>
  <th>Actions</th>
</tr>
<!-- BEGIN listrows -->
<tr {TOGGLE}>
  <td>{NAME}</td>
  <td>{USERNAME}</td>
  <td>{STATUS}</td>
  <td>{ACTIONS}</td>
</tr>
<!-- END listrows -->
<!-- BEGIN EMPTY_MESSAGE -->
<tr>
  <td colspan="42">{EMPTY_MESSAGE}</td>
</tr>
<!-- END EMPTY_MESSAGE -->
</table>

<div align="center">
  <b>{PAGE_LABEL}</b><br />
  {PAGES}<br />
  {LIMITS}
</div>

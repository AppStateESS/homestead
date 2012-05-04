<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}
<br />

<table>
  <tr>
    <th>Assignment Reason</th>
    <th># Assignments</th>
  </tr>

  <!-- BEGIN TABLE_ROWS -->
  <tr>
    <td>{REASON}</td>
    <td>{COUNT}</td>
  </tr>
  <!-- END TABLE_ROWS -->
  
  <tr>
    <td><strong>Total:</strong></td>
    <td><strong>{TOTAL_ASSIGNMENTS}</strong></td>
  </tr>
</table>
<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}
<br />

<table>
  <tr>
    <th>Hall Name</th>
    <th># Check-ins</th>
  </tr>

  <!-- BEGIN TABLE_ROWS -->
  <tr>
    <td>{HALL_NAME}</td>
    <td>{COUNT}</td>
  </tr>
  <!-- END TABLE_ROWS -->
  
  <tr>
    <td><strong>Total:</strong></td>
    <td><strong>{TOTAL_CHECKINS}</strong></td>
  </tr>
</table>
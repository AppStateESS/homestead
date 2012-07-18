<h1>{NAME} <br /> {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}
<br />

<table>
  <tr>
    <th>Cancellation Reason</th>
    <th># Contracts</th>
  </tr>

  <!-- BEGIN TABLE_ROWS -->
  <tr>
    <td>{REASON}</td>
    <td>{COUNT}</td>
  </tr>
  <!-- END TABLE_ROWS -->
  
  <tr>
    <td><strong>Total:</strong></td>
    <td><strong>{TOTAL_CANCELLATIONS}</strong></td>
  </tr>
</table>
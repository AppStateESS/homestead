<h1>{NAME} <br /> {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}
<br />

<h2> All Students</h2>
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

<p>
<strong>Note:</strong>
The Freshmen and Continuing totals shown below will not always sum to the total given above. The total above includes other student types (Transfers, Returning, Re-admit, etc), which are not broken out below.
</p>


<h2>Freshmen Students</h2>

<table>
  <tr>
    <th>Freshmen Cancellation Reason</th>
    <th># Contracts</th>
  </tr>

  <!-- BEGIN FRESHMEN_ROWS -->
  <tr>
    <td>{REASON}</td>
    <td>{COUNT}</td>
  </tr>
  <!-- END FRESHMEN_ROWS -->
  
  <tr>
    <td><strong>Total:</strong></td>
    <td><strong>{FRESHMEN_TOTAL}</strong></td>
  </tr>
</table>


<h2>Continuing Students</h2>

<table>
  <tr>
    <th>Continuing Cancellation Reason</th>
    <th># Contracts</th>
  </tr>

  <!-- BEGIN CONTINUING_ROWS -->
  <tr>
    <td>{REASON}</td>
    <td>{COUNT}</td>
  </tr>
  <!-- END CONTINUING_ROWS -->
  
  <tr>
    <td><strong>Total:</strong></td>
    <td><strong>{CONTINUING_TOTAL}</strong></td>
  </tr>
</table>

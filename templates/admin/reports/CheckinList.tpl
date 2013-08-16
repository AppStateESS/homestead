<h1>{NAME} - {TERM}</h1>

<p>Executed on: {EXEC_DATE} by {EXEC_USER}</p>

  <ul>
    <li>{TOTAL} total check-ins</li>
  </ul>

<table id="needs" cellpadding="2" border="1" style="border-collapse : collapse">
    <tr>
        <th>Banner ID</th>
        <th>Hall</th>
        <th>Room</th>
        <th>Date</th>
    </tr>
<!-- BEGIN rows -->
    <tr>
        <td>{banner_id}</td>
        <td>{hall_name}</td>
        <td>{room_number}</td>
        <td>{checkin_date}</td>
    </tr>
<!-- END rows -->
</table>
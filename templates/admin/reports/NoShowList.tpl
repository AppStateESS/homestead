<h1>{NAME} - {TERM}</h1>

<p>Executed on: {EXEC_DATE} by {EXEC_USER}</p>

  <ul>
    <li>{TOTAL} total check-ins</li>
  </ul>

<table id="needs" border="1" style="border-collapse : collapse">
    <tr>
        <th>Banner ID</th>
        <th>Username</th>
        <th>Assignment Reason</th>
        <th>Class</th>
        <th>Hall</th>
        <th>Room</th>
    </tr>
<!-- BEGIN rows -->
    <tr>
        <td>{banner_id}</td>
        <td>{asu_username}</td>
        <td>{reason}</td>
        <td>{class}</td>
        <td>{hall_name}</td>
        <td>{room_number}</td>
    </tr>
<!-- END rows -->
</table>
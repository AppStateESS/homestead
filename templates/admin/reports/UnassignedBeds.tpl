<h1>{NAME} - {TERM}</h1>

<p>Executed on: {EXEC_DATE} by {EXEC_USER}</p>

<p>
Found {TOTAL_BEDS} empty beds in {TOTAL_ROOMS} rooms:
<ul>
  <li>Male: {MALE}</li>
  <li>Female: {FEMALE}</li>
  <li>Coed: {COED}</li>
</ul>

<table id="needs" width="100%" cellpadding="3" border="1" style="border-collapse : collapse">
    <tr>
        <th>Residence Hall (Total Occ.)</th>
        <th>Male</th>
        <th>Either</th>
        <th>Female</th>
    </tr>
<!-- BEGIN rows -->
    <tr>
        <td>{hallName} ({currOccupancy}/{maxOccupancy})</td>
        <td>{maleRooms}</td>
        <td>{coedRooms}</td>
        <td>{femaleRooms}</td>
    </tr>
<!-- END rows -->
</table>
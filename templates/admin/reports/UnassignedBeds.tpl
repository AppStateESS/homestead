<h2>{NAME} <small>{TERM}</small></h2>

<p>Executed on: {EXEC_DATE} by {EXEC_USER}</p>
<p>
Found <strong>{TOTAL_BEDS}</strong> empty beds in <strong>{TOTAL_ROOMS}</strong> rooms:
<ul>
<li>Male: {MALE}</li>
<li>Female: {FEMALE}</li>
<li>Coed: {COED}</li>
</ul>
<table id="needs" class="table table-hover table-striped">
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

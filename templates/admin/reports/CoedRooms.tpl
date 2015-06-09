<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">
	<div class="row">
		<p class="col-md-6">
			Executed on: {EXEC_DATE} by {EXEC_USER}
		</p>
	</div>

	<div class="row">
		<label class="col-md-3">
			Total Coed Rooms:
		</label>
		<label class="col-md-3 col-md-offset-1">
			{totalCoed}
		</label>
	</div>

<table class="table table-striped table-hover">
	<tr>
		<th>Hall Name</th>
		<th>Floor Number</th>
		<th>Room Number</th>
	</tr>
	<!-- BEGIN rows -->
	<tr>
		<td>{hall_name}</td>
		<td>{floor_number}</td>
		<td>{room_number}</td>
	<!-- END rows -->
</table>

<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">
	<div class="row">
		<div class="col-md-6">
			<p>
				Executed on: {EXEC_DATE} by {EXEC_USER}
			</p>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<p>
				<strong>Total RAs: {TOTAL}</strong>
			</p>
		</div>
	</div>

	<table class="table table-striped table-hover">
		<th>
			Name
		</th>
		<th>
			Banner ID
		</th>
		<th>
			Username
		</th>
		<th>
			Hall
		</th>
		<th>
			Floor
		</th>
		<th>
			Room Number
		</th>
		<!-- BEGIN rows -->
			<tr>
				<td>
					{first_name} {last_name}
				</td>
				<td>
					{banner_id}
				</td>
				<td>
					{email}
				</td>
				<td>
					{hall_name}
				</td>
				<td>
					{floor_number}
				</td>
				<td>
					{room_number}
				</td>
			</tr>
		<!-- END rows -->
	</table>
</div>

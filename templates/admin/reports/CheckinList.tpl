<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">

  <div class="row">
    <p class="col-md-6">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>

  <div class="row">
    <label class="col-md-2">
      Total Check-ins:
    </label>
    <label class="col-md-2 col-md-offset-1">
      {TOTAL}
    </label>
  </div>
  <p></p>

<table class="table table-striped table-hover">
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

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
        <th>Username</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Hall</th>
        <th>Floor</th>
        <th>Room</th>
        <th>Bed</th>
        <th>Checkout By</th>
        <th>Checkout Date</th>
    </tr>
<!-- BEGIN rows -->
    <tr>
        <td>{banner_id}</td>
        <td>{username}</td>
        <td>{first_name}</td>
        <td>{last_name}</td>
        <td>{hall_name}</td>
        <td>{floor_number}</td>
        <td>{room_number}</td>
        <td>{bed}</td>
        <td>{checkout_by}</td>
        <td>{checkout_date}</td>
    </tr>
<!-- END rows -->
</table>

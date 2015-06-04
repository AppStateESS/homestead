<h1>{NAME} <small>{TERM}</small></h1>
<div class="col-md-11">
  <p>
    Executed on: {EXEC_DATE} by {EXEC_USER}
  </p>

  <div class="row">
    <label class="col-md-2">Total no-shows:</label>
    <strong class="col-md-3">{TOTAL}</strong>
  </div>

  <table class="table table-striped table-hover">
    <tr>
        <th>Banner ID</th>
        <th>Username</th>
        <th>Name</th>
        <th>Class</th>
        <th>Assignment</th>
        <th>Assignment Reason</th>
    </tr>

    <!-- BEGIN rows -->
    <tr>
        <td>{banner_id}</td>
        <td>{username}</td>
        <td>{name}</td>
        <td>{class}</td>
        <td>{hall_name} {room_number}-{bed_letter}</td>
        <td>{reason}</td>
    </tr>
    <!-- END rows -->
  </table>
</div>

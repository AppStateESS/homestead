<h2>{NAME} - {TERM}</h2>

<div class="col-md-12">
  <div class="row">
    <p class="col-md-6">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>

  <div class="row">
    <label class="col-md-2">
      Male:
    </label>
    <label class="col-md-1 text-right">
      {MALE}
    </label>
  </div>

  <div class="row">
    <label class="col-md-2">
      Female:
    </label>
    <label class="col-md-1 text-right">
      {FEMALE}
    </label>
  </div>

  <div class="row">
    <label class="col-md-2">
      Total:
    </label>
    <label class="col-md-1 text-right">
      {TOTAL}
    </label>
  </div>

  <table class="table table-striped table-hover">
    <tr>
        <th>Banner ID</th>
        <th>User name</th>
        <th>Gender</th>
        <th>Date</th>
        <th>App Term</th>
        <th>Type</th>
        <th>Meal</th>
        <th>Lifestyle</th>
        <th>Bedtime</th>
        <th>Condition</th>
        <th>Roommate</th>
        <th>Roommate ID</th>
    </tr>
    <!-- BEGIN rows -->
    <tr>
        <td>{banner_id}</td>
        <td>{username}</td>
        <td>{gender}</td>
        <td>{created_on}</td>
        <td>{application_term}</td>
        <td>{student_type}</td>
        <td>{meal_plan}</td>
        <td>{lifestyle_option}</td>
        <td>{preferred_bedtime}</td>
        <td>{room_condition}</td>
        <td>{roommate}</td>
        <td>{roommate_banner_id}</td>
    </tr>
    <!-- END rows -->
  </table>
</div>

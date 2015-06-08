<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">
  <div class="row">
    <p class="col-md-6">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>

  <div class="row">
    <label class="col-md-3">
      <u>Hall Name</u>
    </label>
    <label class="col-md-3 col-md-offset-1">
      <u># of Check-ins</u>
    </label>
  </div>

  <!-- BEGIN TABLE_ROWS -->
  <div class="row">
    <p class="col-md-3">
      {HALL_NAME}
    </p>
    <p class="col-md-3 col-md-offset-1">
      {COUNT}
    </p>
  </div>
  <!-- END TABLE_ROWS -->

  <div class="row">
    <label class="col-md-3">
      Total:
    </label>
    <label class="col-md-3 col-md-offset-1">
      {TOTAL_CHECKINS}
    </label>
  </div>
</div>

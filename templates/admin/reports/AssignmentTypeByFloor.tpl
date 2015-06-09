<h2>{NAME} <small>{TERM}</small></h2>

<p>
  Executed on: {EXEC_DATE} by {EXEC_USER}
</p>

<!-- BEGIN hall_repeat -->
  <div class="row">
    <h2>
      {HALL_NAME}
    </h2>
  </div>

  <div class="row">
    <div class="col-md-6">
        <u>Total Beds: {HALL_OCCUPANCY}/{HALL_CAPACITY}</u>
    </div>
  </div>
  <div class="row">
      <!-- BEGIN hall_totals -->
        <div class="col-md-6">
          <div class="row">
            <div class="col-md-12">
              <div class="col-md-3">
                {HALL_TOTAL_TYPE}:
              </div>
              <div class="col-md-3 col-md-offset-1">
                {HALL_TOTAL_COUNT}
              </div>
            </div>
          </div>
        </div>
        <!-- END hall_totals -->
  </div>

  <div class="row">
    <div class="col-md-6">
      <!-- BEGIN floors -->
      <div class="row">
        <div class="col-md-4">
          <strong>{FLOOR_NUMBER} Floor:</strong>
        </div>
      </div>
      <!-- BEGIN floor_counts -->
      <div class="row">
        <div class="col-md-12">
          <div class="col-md-3">
            {TYPE}:
          </div>
          <div class="col-md-3 col-md-offset-1">
            {COUNT}
          </div>
        </div>
      </div>
      <!-- END floor_counts -->

      <!-- END floors -->
    </div>
  </div>



<!-- END hall_repeat -->

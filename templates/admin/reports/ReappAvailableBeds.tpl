<h2>{NAME} <small>{TERM}</small></h2>

  <div class="col-md-12">
  <div class="row">
    <p class="col-md-8">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>


  <!-- BEGIN halls -->
    <div class="row">
      <div class="col-md-12">
        <h3>
          {HALL_NAME}
        </h3>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3">
        <h4>
          Male beds remaining: {MALE_FREE}
        </h4>
      </div>
      <div class="col-md-3">
        <h4>
          Female beds remaining: {FEMALE_FREE}
        </h4>
      </div>
    </div>

    <div class="col-md-6">
      <table class="table table-striped table-hover">
        <tr>
          <th>
            Room number
          </th>
          <th>
            Gender
          </th>
          <th>
            Beds Remaining
          </th>
        </tr>
        {ROOMS}
      </table>
    </div>
  <!-- END halls -->
  </div>

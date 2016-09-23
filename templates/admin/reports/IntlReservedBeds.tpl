<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">
  <div class="row">
    <p class="col-md-8">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>

      <p><strong>
          Total: {TOTAL}
      </strong></p>


  <!-- BEGIN halls -->
    <div class="row">
      <div class="col-md-12">
        <h3>
          {HALL_NAME} <small>{HALL_TOTAL} Beds Reserved</small>
        </h3>
      </div>
      <div class="col-md-5">
          <table class="table table-striped col-md-6">
              <thead>
                  <th>Room Number</th>
                  <th>Bed</th>
              </thead>
              <tbody>
                  <!-- BEGIN beds -->
                  <tr>
                      <td>{ROOM_NUMBER}</td>
                      <td>{BED}</td>
                  </tr>
                  <!-- END beds -->
              </tbody>
          </table>
      </div>
    </div>
  <!-- END halls -->
</div>

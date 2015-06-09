<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-8">
  Executed on: {EXEC_DATE} by {EXEC_USER}

  <h3> All Students</h3>

    <div class="row">
      <label class="col-md-4">
        <u>Cancellation Reasons</u>
      </label>
      <label class="col-md-3 col-md-offset-1">
        <u># of Contracts</u>
      </label>
    </div>

    <!-- BEGIN TABLE_ROWS -->
      <div class="row">
        <p class="col-md-4">
          {ALL_REASON}
        </p>
        <p class="col-md-3 col-md-offset-1">
          {ALL_COUNT}
        </p>
      </div>
    <!-- END TABLE_ROWS -->

    <div class="row">
      <label class="col-md-4">
        Total:
      </label>
      <label class="col-md-3 col-md-offset-1">
        {TOTAL_CANCELLATIONS}
      </label>
    </div>

    <div class="row">
        <div class="col-md-12">
          <p>
            <strong>Note:</strong>
            The Freshmen and Continuing totals shown below will not always sum to the total given above. The total above includes other student types (Transfers, Returning, Re-admit, etc), which are not broken out below.
          </p>
        </div>
    </div>

  <h3>Freshmen Students</h3>

    <div class="row">
      <label class="col-md-4">
        <u>Cancellation Reasons</u>
      </label>
      <label class="col-md-3 col-md-offset-1">
        <u># of Contracts</u>
      </label>
    </div>

    <!-- BEGIN FRESHMEN_ROWS -->
      <div class="row">
        <p class="col-md-4">
          {FR_REASON}
        </p>
        <p class="col-md-3 col-md-offset-1">
          {FR_COUNT}
        </p>
      </div>
    <!-- END FRESHMEN_ROWS -->

    <div class="row">
      <label class="col-md-4">
        Total:
      </label>
      <label class="col-md-3 col-md-offset-1">
        {FRESHMEN_TOTAL}
      </label>
    </div>


  <h3>Continuing Students</h3>

    <div class="row">
      <label class="col-md-4">
        <u>Cancellation Reasons</u>
      </label>
      <label class="col-md-3 col-md-offset-1">
        <u># of Contracts</u>
      </label>
    </div>

    <!-- BEGIN CONTINUING_ROWS -->
      <div class="row">
        <p class="col-md-4">{C_REASON}</p>
        <p class="col-md-3 col-md-offset-1">{C_COUNT}</p>
      </div>
    <!-- END CONTINUING_ROWS -->

    <div class="row">
      <label class="col-md-4">Total:</label>
      <label class="col-md-3 col-md-offset-1">{CONTINUING_TOTAL}</label>
    </div>

</div>

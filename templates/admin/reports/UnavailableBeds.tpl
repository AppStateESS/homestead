<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">
  <div class="row">
    <p class="col-md-12">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>

  <div class="row">
    <label class="col-md-3">
      <u>Beds in system</u>
    </label>
  </div>

  <div class="row">
    <label class="col-md-2">
      Unavailable:
    </label>
    <label class="col-md-2">
      {UNAVAILABLE_BEDS}
    </label>
  </div>

  <div class="row">
    <label class="col-md-2">
      Available:
    </label>
    <label class="col-md-2">
      {AVAILABLE_BEDS}
    </label>
  </div>

  <div class="row">
    <label class="col-md-2">
      Total:
    </label>
    <label class="col-md-2">
      {TOTAL_BEDS}
    </label>
  </div>

  <table class="table table-striped table-hover">
    <tr>
      <th><strong>Totals</strong></th>
      <th></th>
      <th></th>
      <th>Reserved</th>
      <th>RA</th>
      <th>RA Roommate</th>
      <th>Private</th>
      <th>Overflow</th>
      <th>Parlor</th>
      <th>Int'l Reserved</th>
      <th>Offline</th>
    </tr>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td>{RESERVED_TOTAL}</td>
      <td>{RA_TOTAL}</td>
      <td>{RA_ROOMMATE_TOTAL}</td>
      <td>{PRIVATE_TOTAL}</td>
      <td>{OVERFLOW_TOTAL}</td>
      <td>{PARLOR_TOTAL}</td>
      <td>{INTL_TOTAL}</td>
      <td>{OFFLINE_TOTAL}</td>
    </tr>
    <tr>
      <th>Hall</th>
      <th>Room #</th>
      <th>Bed</th>
      <th>Reserved</th>
      <th>RA</th>
      <th>RA Roommate</th>
      <th>Private</th>
      <th>Overflow</th>
      <th>Parlor</th>
      <th>Int'l Reserved</th>
      <th>Offline</th>
    </tr>
    <!-- BEGIN bed_rows -->
    <tr>
      <td>{HALL}</td>
      <td>{ROOM}</td>
      <td>{BED_LETTER}</td>
      <td>{RESERVED}</td>
      <td>{RA}</td>
      <td>{RA_ROOMMATE}</td>
      <td>{PRIVATE}</td>
      <td>{OVERFLOW}</td>
      <td>{PARLOR}</td>
      <td>{INTL}</td>
      <td>{OFFLINE}</td>
    </tr>
    <!-- END bed_rows -->

  </table>
</div>

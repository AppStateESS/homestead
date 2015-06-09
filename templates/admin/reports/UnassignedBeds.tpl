<h2>{NAME} <small>{TERM}</small></h2>

<div class="col-md-12">
  <div class="row">
    <p class="col-md-6">
      Executed on: {EXEC_DATE} by {EXEC_USER}
    </p>
  </div>

  <div class="row">
    <p class="col-md-6">
      Found {TOTAL_BEDS} empty beds in {TOTAL_ROOMS} rooms:
    </p>
  </div>

  <div class="row">
    <label class="col-md-2">
      Male:
    </label>
    <label class="col-md-2">
      {MALE}
    </label>
  </div>

  <div class="row">
    <label class="col-md-2">
      Female:
    </label>
    <label class="col-md-2">
      {FEMALE}
    </label>
  </div>

  <div class="row">
    <label class="col-md-2">
      Coed:
    </label>
    <label class="col-md-2">
      {COED}
    </label>
  </div>

  <!-- BEGIN rows -->
  <div class="row">
    <h3>
      {hallName} ({currOccupancy}/{maxOccupancy}):
    </h3>
  </div>

  <div class="row">
    <label class="col-md-2">
      Male Only:
    </label>
    <p class="col-md-6">
      {maleRooms}
    </p>
  </div>

  <div class="row">
    <label class="col-md-2">
      Female Only:
    </label>
    <p class="col-md-6">
      {femaleRooms}
    </p>
  </div>
  <div class="row">
    <label class="col-md-2">
      Either:
    </label>
    <p class="col-md-6">
      {coedRooms}
    </p>
  </div>
  <!-- END rows -->

</div>

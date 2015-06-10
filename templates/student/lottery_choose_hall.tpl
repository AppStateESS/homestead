<h2>Choose a Residence Hall</h2>

<div class="col-md-12">
  <div class="row">
    <p class="col-md-8">
      Congratulations, you have been selected for on-campus housing for {TERM}!
    </p>

    <p class="col-md-8">
      You may select any room which is currently available. Browse available rooms by selecting a residence hall below.
    </p>
  </div>

  {START_FORM}

  <div class="row">
    <div class="col-md-3">
      {HALL_CHOICES}
    </div>
  </div>

  <div class="row">
    <ul>
      <!-- BEGIN hall_list -->
        <li class="{ROW_TEXT_COLOR}">
          {HALL_NAME}
        </li>
        <!-- END hall_list -->
    </ul>
  </div>

  <!-- BEGIN nothing_left -->
  <div class="row">
    <div class="col-md-8">
      {NOTHING_LEFT}
      <p>
        <strong>Oops!</strong>
        It looks like there's no remaining beds in your assigned Residential Learning Community.
        For more detail, you may want to contact University Housing by calling 828-262-6111.
      </p>
    </div>
  </div>
  <!-- END nothing_left -->

  <div class="row">
    <div class="col-md-3">
      <a href="index.php" class="btn btn-danger btn-lg pull-left">
        <i class="fa fa-chevron-left"></i>
        Cancel
      </a>
      <!-- BEGIN available -->
      {AVAILABLE}
      <button type="submit" class="btn btn-lg btn-success pull-right">
        Continue
        <i class="fa fa-chevron-right"></i>
      </button>
      <!-- END available -->
    </div>
  </div>

  {END_FORM}

</div>

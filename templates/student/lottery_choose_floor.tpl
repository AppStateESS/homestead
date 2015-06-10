<h2>{HALL} - Choose floor</h2>

<div class="col-md-12">

{START_FORM}

<div class="row">
  <div class="col-md-8">
    {EXTERIOR_IMAGE}
    <!-- commenting out for now
          <div style="border: 1px solid #AAAAAA;">
                <h2>Hall Features</h2>
                <ul>
                    <li>One</li>
                    <li>Two</li>
                    <li>Three</li>
                </ul>
            </div>
            -->
            {ROOM_PLAN_IMAGE}
            {MAP_IMAGE}
            {OTHER_IMAGE}
    </div>
  </div>

  <div class="row">
    <p class="col-md-8">
      Please select a floor from the list below. Unavailable floors are shown in grey. Click the images to the right to view a larger version.
    </p>
  </div>

  <div class="row">
    <div class="col-md-3">
      {FLOOR_CHOICES}
    </div>
  </div>

  <p>
  </p>

  <div class="row">
    <div class="col-md-3">
      <a href="index.php" class="btn btn-danger btn-lg pull-left">
        <i class="fa fa-chevron-left"></i>
        Cancel
      </a>
      <button type="submit" class="btn btn-lg btn-success pull-right">
        Continue
        <i class="fa fa-chevron-right"></i>
      </button>
    </div>
  </div>

</div>

{END_FORM}

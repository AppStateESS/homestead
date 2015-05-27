
<a href="{MENU_LINK}" class="btn btn-lg btn-primary">
  <i class="fa fa-chevron-left"></i>
  Return to RLC Applicants
</a>

<h2>Learning Community Application</h2>

<div class="col-md-12">
  <!-- BEGIN rlc_list -->
    <div class="col-md-4 pull-right">
      <div class="panel panel-primary">
        <div class="panel-heading">
          Assignment
        </div>
        <div class="panel-body">
          {START_FORM}
          <div class="row">
            <!-- <div class="col-md-4"> -->
              {RLC_LIST}
            <!-- </div> -->
          </div>
          <p></p>
          <div class="row">
            {APPROVE}
            <div class="pull-right">
              {DENY_APP}
            </div>
          </div>
          {END_FORM}
        </div>
      </div>
    </div>
  <!-- END rlc_list -->

  <div class="row">
    <div class="col-md-6">
      <div class="row">
        <h3>
          {FULL_NAME}
        </h3>
      </div>

      <div class="row">
        <label class="col-md-3">
          Term:
        </label>
        <div class="col-md-3 col-md-offset-3">
          {TERM}
        </div>
      </div>

      <div class="row">
        <label class="col-md-3">
          Student Type:
        </label>
        <div class="col-md-3 col-md-offset-3">
          {STUDENT_TYPE}
        </div>
      </div>

      <div class="row">
        <label class="col-md-4">
          Application Type:
        </label>
        <div class="col-md-3 col-md-offset-2">
          {APPLICATION_TYPE}
        </div>
      </div>

      <div class="row">
        <label class="col-md-4">
          RLC Preferences:
        </label>
        <div class="col-md-6">
          <ol>
            <li>
              {FIRST_CHOICE}
            </li>
            <!-- BEGIN second_choice -->
            <li>
              {SECOND_CHOICE}
            </li>
            <!-- END second_choice -->
            <!-- BEGIN third_choice -->
            <li>
              {THIRD_CHOICE}
            </li>
            <!-- END third_choice -->
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <label class="col-md-6">
      Specific communities chosen because:
    </label>
  </div>

  <p class="col-md-8">
    {WHY_SPECIFIC}
  </p>

  <div class="row">
    <label class="col-md-6">
      Strengths and weaknesses:
    </label>
  </div>

  <p class="col-md-8">
    {STRENGTHS_AND_WEAKNESSES}
  </p>

  <div class="row">
    <label class="col-md-6">
      Chose {FIRST_CHOICE} because:
    </label>
  </div>

  <p class="col-md-8">
    {WHY_FIRST_CHOICE}
  </p>

  <!-- BEGIN second -->
  <div class="row">
    <label class="col-md-6">
      Chose {SECOND_CHOICE} because:
    </label>
  </div>

  <p class="col-md-8">
    {WHY_SECOND_CHOICE}
  </p>
  <!-- END second -->

  <!-- BEGIN third -->
  <div class="row">
    <label class="col-md-6">
      Chose {THIRD_CHOICE} because:
    </label>
  </div>

  <p class="col-md-8">
    {WHY_THIRD_CHOICE}
  </p>
  <!-- END third -->

</div>

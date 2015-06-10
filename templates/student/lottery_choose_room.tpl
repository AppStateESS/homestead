<h2>{HALL_FLOOR}</h2>

<div class="row">
  <!-- BEGIN error_msg -->
    <span class="error">{ERROR_MSG}<br /></span>
  <!-- END error_msg -->
</div>

<div class="row">
  <!-- BEGIN success_msg -->
    <span class="success">{SUCCESS_MSG}<br /></span>
  <!-- END success_msg -->
</div>

<div class="row">
  {FLOOR_PLAN_IMAGE}
</div>

<div class="row">
  <p class="col-md-8">
    Choose a room from the list below by clicking on its room number. Rooms which are unavailable to you are shown in grey. Click the floor plan image to the right to see a larger version.
  </p>
</div>

      <table class="table table-striped table-hover">
        <tr>
          <th>Room</th>
          <th>Available beds</th>
          <th># of Beds</th>
        </tr>
        <!-- BEGIN room_list -->
        <tr class="{ROW_TEXT_COLOR}">
          <td>{ROOM_NUM}</td>
          <td>{AVAIL_BEDS}</td>
          <td>{NUM_BEDS}</td>
        </tr>
        <!-- END room_list -->
      </table>
</div>

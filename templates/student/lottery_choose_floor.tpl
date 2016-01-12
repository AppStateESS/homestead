<h2>{HALL} - Choose floor</h2>

<div class="col-md-12">
  <!-- BEGIN error_msg -->
    <span class="error">{ERROR_MSG}<br /></span>
  <!-- END error_msg -->

  <!-- BEGIN success_msg -->
    <span class="success">{SUCCESS_MSG}<br /></span>
  <!-- END success_msg -->

  <div style="float: right; height: 500; width: 300;">
     {EXTERIOR_IMAGE}<br /><br />
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
    <br /><br />
    {ROOM_PLAN_IMAGE}<br />
    {MAP_IMAGE}<br />
    {OTHER_IMAGE}
  </div>

  <p>
    Please select a floor from the list below. Unavailable floors are shown in grey. Click the images to the right to view a larger version.
  </p>

  <div class="col-md-2">
    <table class="table table-striped table-bordered">
      <tr>
        <th class="text-center">
          Floor
        </th>
      </tr>
      <!-- BEGIN floor_list -->
        <tr {ROW_TEXT_COLOR}>
          <td class="text-center">{FLOOR}</td>
        </tr>
        <!-- END floor_list -->
    </table>
  </div>
</div>

<div id="cancelDialog"></div>

<div id="applications">
  <table class="table table-striped">
    <tr>
      <th>Term</th>
      <th>Type</th>
      <th>Cell phone #</th>
      <th>Meal plan</th>
      <th>Cleanliness</th>
      <th>Bedtime</th>
      <th>Actions</th>
    </tr>
    <!-- BEGIN APPLICATIONS -->
    <tr class="{row_style}">
      <td>{term}</td>
      <td>{type}</td>
      <td>{cell_phone}</td>
      <td>{meal_plan}</td>
      <td>{clean}</td>
      <td>{bedtime}</td>
      <td><a href="{view}" class="btn btn-primary btn-xs"><i class="fa fa-search"></i> View</a>
          <!-- BEGIN cancel-link -->
          <a href="{cancel}" class="btn btn-danger btn-xs cancelAppLink"><i class="fa fa-times"></i> Cancel</a>
          <!-- END cancel-link -->
      </td>
    </tr>
    <!-- END APPLICATIONS -->
    <!-- BEGIN no_apps -->
    <tr>
      <td colspan="5">{APPLICATIONS_EMPTY}</td>
    </tr>
    <!-- END no_apps -->
  </table>
</div>

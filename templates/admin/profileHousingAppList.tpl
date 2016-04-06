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
      <td><div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"
                id="dropdownMenu" aria-haspopup="true" aria-expanded="false">
                Actions
                <span class="caret">
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu">
                <li>
                    <a href="{view}"><i class="fa fa-search"></i> View Application</a>
                </li>
                <!-- BEGIN cancel-link -->
                <li>
                    <a href="{cancel}"><i class="fa fa-times"></i> Cancel Application</a>
                </li>
                <!-- END cancel-link -->
                <!-- BEGIN reinstate-link -->
                <li>
                    <a href="{reinstate}"><i class="fa fa-undo"></i> Re-instate</a>
                </li>
                <!-- END reinstate-link -->
                <!-- BEGIN view_contract-link -->
                <li role="separator" class="divider"></li>
                <li>
                    <a href="{contract}"><i class="fa fa-file-text"></i> View Contract</a>
                </li>
                <!-- END view_contract-link -->
            </ul>
          </div>
            <!-- BEGIN cancelled -->
                <span class="text-muted">{cancelledReason}</span>
            <!-- END cancelled -->
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

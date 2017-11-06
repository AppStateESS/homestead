<h2 style="margin-top:0;">Room Assignments</h2>

<div class="btn-group pull-right">
  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fa fa-cog"></i> Maintenance <span class="caret"></span>
  </button>
  <ul class="dropdown-menu">
    <li><a href="{HALL_OVERVIEW_URI}">Hall Overview</a></li>
    <li><a href="{ROOM_CHANGE_URI}">Room Change Requests</a></li>
    <li role="separator" class="divider"></li>
    <li><a href="{ASSIGN_BY_FLOOR_URI}">Bulk Assign by Floor</a></li>
    <li><a href="{AUTO_ASSIGN_URI}">Auto-assign</a></li>
    <li><a href="{WITHDRAWN_SEARCH_URI}">Withdrawn Search</a></li>
  </ul>
</div>

<div class="form-group">
    <a href="{ASSIGN_STUDENT_URI}" class="btn btn-primary"><i class="fa fa-plus"></i> Assign Student</a>
</div>

<div id="assignmentsTable"></div>

<script type="text/javascript" src="{vendor_bundle}"></script>
<script type="text/javascript" src="{entry_bundle}"></script>

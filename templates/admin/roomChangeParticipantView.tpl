<div>
  <h3>{NAME} ({BANNER_ID})</h3>
  
  <p>
    Cell phone: {CELL_PHONE}
  
    <!-- BEGIN hall_pref -->
    <br />Preferences: {HALL_PREF}
    <!-- END hall_pref -->
  </p>
  
  <strong>From</strong> {FROM_ROOM}
  <!-- BEGIN to_room -->
  <strong>To</strong> {TO_ROOM}
  <!-- END to_room -->
  
  <br />
  
  {START_FORM}
  <!-- BEGIN to_select -->
  <strong>To</strong> {BED_SELECT}
  <!-- END to_select -->
  
  <!-- BEGIN approve_btn -->
  {APPROVE_BTN}
  <button type="submit" class="btn btn-primary">Approve</button>
  <!-- END approve_btn -->
  
  {END_FORM}
</div>
<div style="margin-top:1em;">
<strong>Approval History</strong><br />
<ul>
<!-- BEGIN history_rows -->
<li>{STATE_NAME} on {EFFECTIVE_DATE} by {COMMITTED_BY}</li>
<!-- END history_rows -->
</ul>
</div>
<hr>
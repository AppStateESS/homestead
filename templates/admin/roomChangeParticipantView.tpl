<div>
  <h3>{NAME} ({BANNER_ID})</h3>
  
  <p>
    Cell phone: {CELL_PHONE}
  
    <!-- BEGIN hall_pref -->
    <br />Preferences: {HALL_PREF}
    <!-- END hall_pref -->
  </p>
  
  <strong>From</strong> {FROM_ROOM}<br />
  
  {START_FORM}
  <!-- BEGIN to_room -->
  <strong>To</strong> {TO_ROOM}<br />
  <!-- END to_room -->
  
  <!-- BEGIN to_select -->
  <strong>To</strong> {BED_SELECT}
  <!-- END to_select -->
  
  <!-- BEGIN approve_btn -->
  {APPROVE_BTN}
  <button type="submit" class="btn btn-primary">Approve</button>
  <!-- END approve_btn -->
  
  {END_FORM}
</div>
<hr>
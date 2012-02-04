<h3>{TERM} &raquo; {HALL_NAME} &raquo; {FLOOR_NUMBER}</h3>
<!-- BEGIN room_num -->
<h1>Room {ROOM}</h1>
<!-- END room_num -->
<!-- BEGIN new -->{NEW_ROOM}
<h1>New Room</h1>
<!-- END new -->
{START_FORM}

<div style="width:200px; float:right;">
  <div class="rounded-box">
    <div class="boxheader">
      <h2 style="padding: 3px;">Status</h2>
    </div>
    <div style="padding: 3px;">
      {OFFLINE} {OFFLINE_LABEL}<br /> {RESERVED} {RESERVED_LABEL}<br />
      {RA} {RA_LABEL}<br /> {PRIVATE} {PRIVATE_LABEL}<br /> {OVERFLOW}
      {OVERFLOW_LABEL}<br /> {PARLOR} {PARLOR_LABEL}<br /> <strong>Medical</strong>
      <div style="margin-left: 15px;">
        {ADA} {ADA_LABEL}<br /> {HEARING_IMPAIRED}
        {HEARING_IMPAIRED_LABEL}<br /> {BATH_EN_SUITE}
        {BATH_EN_SUITE_LABEL}<br />
      </div>
    </div>
  </div>
    <br />
    {SUBMIT} {END_FORM}
</div>

<div class="rounded-box" style="width: 450px;float:left;">
  <div class="boxheader">
    <h2 style="padding: 3px;">Settings</h2>
  </div>
  <div style="padding: 3px;">
    Room Number: {ROOM_NUMBER}<br /> Gender:
    <!-- BEGIN gender_message -->
    {GENDER_MESSAGE} {GENDER_REASON}
    <!-- END gender_message -->
    {GENDER_TYPE} <br />
    Default Gender: {DEFAULT_GENDER}
  </div>
</div>

<!-- BEGIN occupancy -->
<div class="rounded-box" style="width: 450px; float: left;">
  <div class="boxheader">
    <h2 style="padding: 3px;">Beds</h2>
  </div>
  <div style="padding: 3px;">{NUMBER_OF_ASSIGNEES} of
    {NUMBER_OF_BEDS} occupied {BED_PAGER}</div>
</div>
<!-- END occupancy -->

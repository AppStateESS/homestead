<h3>{TERM} &raquo; {HALL_NAME} &raquo; {FLOOR_NUMBER}</h3>
<h1 style="display: inline; margin-right: 15px">Room {ROOM}</h1>

<!-- BEGIN offline -->
<h2 class="room-attribute offline-room-label">{OFFLINE_ATTRIB}</h2>
<!-- END offline -->

<!-- BEGIN reserved -->
<h2 class="room-attribute reserved-room-label">{RESERVED_ATTRIB}</h2>
<!-- END reserved -->

<!-- BEGIN RA -->
<h2 class="room-attribute ra-room-label">{RA_ATTRIB}</h2>
<!-- END RA -->

<!-- BEGIN private -->
<h2 class="room-attribute private-room-label">{PRIVATE_ATTRIB}</h2>
<!-- END private -->

<!-- BEGIN overflow -->
<h2 class="room-attribute overflow-room-label">{OVERFLOW_ATTRIB}</h2>
<!-- END overflow -->

<!-- BEGIN parlor -->
<h2 class="room-attribute parlor-room-label">{PARLOR_ATTRIB}</h2>
<!-- END parlor -->

<!-- BEGIN ada -->
<h2 class="room-attribute ada-room-label">{ADA_ATTRIB}</h2>
<!-- END ada -->

<!-- BEGIN hearing -->
<h2 class="room-attribute hearing-impaired-room-label">{HEARING_ATTRIB}</h2>
<!-- END hearing -->

<!-- BEGIN bath -->
<h2 class="room-attribute bath-en-suite-room-label">{BATHENSUITE_ATTRIB}</h2>
<!-- END bath -->

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
    {SUBMIT}
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
{END_FORM}
<!-- BEGIN occupancy -->
<div class="rounded-box" style="width: 450px; float: left;">
  <div class="boxheader">
    <h2 style="padding: 3px;">Beds</h2>
  </div>
  <div style="padding: 3px;">{NUMBER_OF_ASSIGNEES} of
    {NUMBER_OF_BEDS} occupied {BED_PAGER}</div>
</div>
<!-- END occupancy -->

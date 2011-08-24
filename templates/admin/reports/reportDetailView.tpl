<h1 class="rrze-report-32">{NAME}</h1>

<div class="rounded-box">
  <div class="boxheader emptyheader">&nbsp;</div>
  <p>Report details go here....</p>
  <!-- BEGIN last-run-relative -->
  <p>Last executed {LAST_RUN_RELATIVE} by <span class="italic">{LAST_RUN_USER}</span>.</p>
  <!-- END last-run-relative -->
  <!-- BEGIN never-run -->{NEVER_RUN}
  <p>This report has never been executed.</p>
  <!-- END never-run -->
</div>

<div id="twocol-main">
  <div class="rounded-box">
    <div class="boxheader">
      <h2 class="rrze-download-32">Archived Results</h2>
    </div>
    {RESULTS_PAGER}
  </div>
</div>

<div id="twocol-side">
  <div class="rounded-box">
    <div class="boxheader">
      <h2 class="rrze-report-run-22">Schedule & Execute</h2>
    </div>
    <ul>
      <!-- BEGIN run-now -->
      <li>{RUN_NOW}</li>
      <!-- END run-now -->
      <!-- BEGIN run-now-diabled -->
      {RUN_NOW_DISABLED}
      <li class="disabledText">Run now not allowed</li>
      <!-- END run-now-diabled -->

      <!-- BEGIN run-bg -->
      <li>{RUN_BACKGROUND}</li>
      <!-- END run-bg -->
      <!-- BEGIN run-bg-disabled -->
      {RUN_BACKGROUND_DISABLED}
      <li class="disabledText">Background execution not allowed</li>
      <!-- END run-bg-disabled -->

      <!-- BEGIN run-schedule -->
      <li>{RUN_SCHEDULE}</li>
      <!-- END run-schedule -->
      <!-- BEGIN run-schedule-disabled -->
      {RUN_SCHEDULE_DISABLED}
      <li class="disabledText">Scheduled execution not allowed</li>
      <!-- END run-schedule-disabled -->
    </ul>
  </div>

  <div class="rounded-box">
    <div class="boxheader">
      <h2 class="rrze-awaiting-22">Pending</h2>
    </div>
    {SCHEDULE_PAGER}
  </div>
</div>

<!-- BEGIN CONTENT -->
<div class="quick">
    <h2>{TITLE}</h2>

<!-- BEGIN error_msg -->
    <span class="error">{ERROR}</span><br/>
<!-- END error_msg -->

    <ul>
    
<!-- BEGIN progress -->

<!-- BEGIN COMPLETED -->
        <li style="color: #417C2C;">{STEP_COMPLETED}</li>
<!-- END COMPLETED -->
<!-- BEGIN CURRENT -->
        <li style="font-weight: bold;">{STEP_CURRENT}</li>
<!-- END CURRENT -->
<!-- BEGIN TOGO -->
        <li>{STEP_TOGO}</td>
<!-- END TOGO -->
<!-- BEGIN NOTYET -->
        <li style="color: #999;">{STEP_NOTYET}</td>
<!-- END NOTYET -->
<!-- BEGIN MISSED -->
        <li style="color: #F00;">{STEP_MISSED}</li>
<!-- END MISSED -->
<!-- BEGIN OPT_MISSED -->
        <li style="color: #CCC;">{STEP_OPT_MISSED}</li>
<!-- END OPT_MISSED -->

<!-- END progress -->

    </ul>
</div>

<script type="text/javascript">

$(document).ready(function() {
    $('#special_interest_group').bind('change', function(){
        $('#special_interest').submit();
    });
});

</script>

<div class="hms">
  <div class="box">
    <div class="box-title"><h1>Special Interest Group Approval</h1></div>
    <div class="box-content">
        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
        {START_FORM}
        Group: {GROUP}
        {END_FORM}
        <br /><br />
        {GROUP_PAGER}
    </div>
  </div>
</div>

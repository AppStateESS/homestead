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
        {START_FORM}
        Group: {GROUP}
        {END_FORM}
        <br /><br />
        {GROUP_PAGER}
    </div>
  </div>
</div>

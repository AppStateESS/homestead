<script type="text/javascript">

    $(document).ready(function() {
        $('#special_interest_group').bind('change', function() {
            $('#special_interest').submit();
        });
    });

</script>

<h2>Special Interest Group Approval</h2>
{START_FORM}
<div class="form-group row">
    <div class="col-sm-1">
        <label for="special_interest_group">Group:</label>
    </div>
    <div class="col-sm-6">
        {GROUP}
    </div>
</div>
{END_FORM}
<br /><br />
{GROUP_PAGER}

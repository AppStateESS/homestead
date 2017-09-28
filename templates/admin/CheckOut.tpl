<style>
    .checkout-error-border {
        border : 1px solid #BC3B3B;
        border-radius : 5px;
    }
</style>
<script type="text/javascript">
    var residents = {RESIDENTS};
    var existing_damage = {EXISTING_DAMAGE};
    var previous_key_code = '{$PREVIOUS_KEY_CODE}';
    var damage_types = {DAMAGE_TYPES};
    var room_pid = '{ROOM_PID}';
    var checkin_id = '{CHECKIN_ID}';
    var banner_id = '{BANNER_ID}';
</script>
<h2>
    <span class="text-primary">{STUDENT}</span> <span class="text-muted">({BANNER_ID})</span> <span style="font-size : .8em; font-weight:normal">checking out of</span> <span class="text-primary">{HALL_NAME}</span>
</h2>
<hr />


<!-- see CheckOut.jsx -->
<div class="container">
    <div id="checkout"></div>
</div>
<script type="text/javascript" src="{vendor_bundle}"></script>
<script type="text/javascript" src="{entry_bundle}"></script>

<script type="text/javascript">

$('document').ready(function() {
    $('#term_selector_term').change(function(e) {
    	$.removeCookie("hms-checkin-hall-id");
        $('#term_selector').submit();
    });
});

</script>
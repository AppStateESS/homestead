<script type="text/javascript" src="javascript/modules/hms/new_autosuggest/bsn.AutoSuggest_2.1.3.js"></script>
<script type="text/javascript" src="javascript/modules/hms/floor_assignment/assigner.js"></script>
<script type="text/javascript">
    var dropbox = '';
    $(document).ready(function(){
        $.post('index.php', {module: 'hms', action: 'GetMealPlanDropbox'}, function(data){
            dropbox = data;

            $(".assign-me").each(function(){
                var assigner = new AssignWidget(this);
                assigner.setup();
            });
        });
    });
</script>

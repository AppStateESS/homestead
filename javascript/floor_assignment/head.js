<script type="text/javascript" src="mod/hms/javascript/new_autosuggest/bsn.AutoSuggest_2.1.3.js"></script>
<script type="text/javascript" src="mod/hms/javascript/javascript/floor_assignment/assigner.js"></script>
<script type="text/javascript">
    var dropbox = '';
    var semaphore = new Semaphore(1);
    $(document).ready(function(){
        $.post('index.php', {module: 'hms', action: 'GetMealPlanDropbox'}, function(data){
            dropbox = data;

            $(".assign-me").each(function(){
                var assigner = new AssignWidget(this, semaphore);
                assigner.setup();
            });
        });
    });
</script>

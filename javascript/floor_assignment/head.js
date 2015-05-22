<script type="text/javascript" src="mod/hms/javascript/new_autosuggest/bsn.AutoSuggest_2.1.3.js"></script>
<script type="text/javascript" src="mod/hms/javascript/floor_assignment/assigner.js"></script>
<script type="text/javascript">
/**
 * Meal plan selection box
 * @type String
 */
    var dropbox = '';
    
    /**
     * Assignment type string
     * @type String
     */
    var assignmentBox = '';
    var semaphore = new Semaphore(1);
    $(document).ready(function(){
    	$.get('index.php', {module: 'hms', action: 'GetAssignmentTypeDropbox'}, function(data) {
            assignmentBox = data;
        });
    	
    	$.post('index.php', {module: 'hms', action: 'GetMealPlanDropbox'}, function(data){
            dropbox = data;
            $(".assign-me").each(function(){
                var assigner = new AssignWidget(this, semaphore);
                assigner.setup();
            });
        });
    });
</script>

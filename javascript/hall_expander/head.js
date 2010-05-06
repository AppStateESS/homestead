<script type="text/javascript" src="javascript/modules/hms/hall_expander/expander.js"></script>
<script type="text/javascript">
var submitHallList = function(e){
    var data = new Array();
    $("#{DIV} :checked").each(
        function(i, element){
            var newField = document.createElement('input');
            newField.setAttribute('name', $(element).attr('objtype')+'[]');
            newField.setAttribute('value', $(element).attr('ref'));
            document.getElementById("{FORM}").appendChild(newField);
        }
    );

    return true;
}

$(document).ready(function(){
    $.post('index.php', {module: 'hms', action: 'ListAllowedHalls'},
        function(data){
            var halls = new Array();
            for(var i in data){
                var newHall = new hall();
                newHall.load(data[i]);
                halls.push(newHall);
            }

            var output = "<ul>";
            for(var i in halls){
                output += '<li class="container expanded">'+halls[i].draw()+"</li>";
            }
            output += "</ul>";
            $("#{DIV}").empty();
            $("#{DIV}").append(output);
            $("#{DIV} li").click(function(){$(this).find('.subtree').toggle();});
            $("#{DIV} :checkbox").click(function(e){e.stopPropagation();});
            $("#{DIV} li .hall").each(
                function(){
                    var subBoxes = $(this).find('.subtree :checkbox');

                    $(this).find(":checkbox[objtype='hall']").each(
                        function(){
                            $(this).click(
                                function(){
                                    var value = $(this).attr('checked');
                                    $(subBoxes).each(
                                        function(){
                                            if(!$(this).attr('disabled'))
                                                $(this).attr('checked', value);
                                        });
                                });
                        });
                });
            
            $("#{DIV} li").toggle(
                function(){
                    $(this).removeClass("expanded");
                    $(this).addClass("collapsed");
                },
                function(){
                    $(this).removeClass("collapsed");
                    $(this).addClass("expanded");
                }
            );
        },
        'json'
    );
});
</script>

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
 };

var autocollapse = function(){
    $(".collapsed").each(function(e){
            //simulates a click event to set up the initial state of the item
            $(this).click();
        });
};

$(document).ready(function(){
        $("#select_all").click(function(){
                $("#hall_list :checkbox:enabled").each(function(){
                        $(this).attr('checked', true);
                    });
                return false;
            });

        $("#select_none").click(function(){
                $("#hall_list :checkbox:enabled").each(function(){
                        $(this).attr('checked', false);
                    });
                return false;
            });

	    //put up an AJAX spinner
        $("#{DIV}").html('<img src="images/core/ajax-loader-big.gif" />');
        
    $.post('index.php', {module: 'hms', action: 'ListAllowedHalls'},
        function(data){
    		$("#{DIV}").empty();
    		
    		if(data.error !== undefined){
    			$("#{DIV}").append('<div class="hms-notification error">'+data.error+'</div>');
    			return;
    		}
    	
            var halls = new Array();
            for(var i in data){
                var newHall = new hall();
                newHall.load(data[i]);
                halls.push(newHall);
            }

            var output = "<ul>";
            for(var i in halls){
                var tmp = halls[i].draw();
                output += '<li class="container '+(halls[i].collapsed ? 'collapsed' : 'expanded' )+'">'+tmp+"</li>";
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
            
            $("#{DIV} li").click(
                function(){
                    if(!this.flip)
                        this.flip = 0;

                    if(this.flip % 2 == 0){
                        $(this).removeClass("expanded");
                        $(this).addClass("collapsed");
                        $(this).find(".subtree").hide();
                    } else {
                        $(this).removeClass("collapsed");
                        $(this).addClass("expanded");
                        $(this).find(".subtree").show();
                    }
                    this.flip++;
                }
            );

            autocollapse();
        },
        'json'
    );
});
</script>

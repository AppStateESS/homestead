<script type="text/javascript">
var state = true;
var eColor = "#000000";
var dColor = "#888888";
var toggle = function(){
    if(state = !state){
        // Enable checkboxes and restore black text
        $(".copy-pick-sub>input").attr("disabled", null);
        $(".copy-pick-sub").css("color", eColor);
    }else{
        // Uncheck boxes, disable checkboxes and grey out text
        $(".copy-pick-sub>input").attr("checked", false);
        $(".copy-pick-sub>input").attr("disabled", "disabled");
        $(".copy-pick-sub").css("color", dColor);
    }
}
$(document).ready(function(){
    // Initialize state. If you set to true above
    // then it breaks if use goes back.
    state = !$(".copy-pick>input").attr("checked");
    // Disable checkboxes by default
    toggle();

    $(".copy-pick>input").click(function(){
        toggle();
    });
});
</script>

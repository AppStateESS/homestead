// Setup autocomplete on the search input
$("#checkin_form_banner_id").autocomplete({
	source : "index.php?module=hms&action=AjaxGetUsernameSuggestions&ajax=1",
	delay : 500,
	minLength : 3,
	focus : function(event, ui) {
		console.log(ui.item);
		console.log($("#checkin_form_banner_id"));
		event.preventDefault(); // NB: Makes moving the focus with the keyboard
								// work
		$("#checkin_form_banner_id").val(ui.item.banner_id);
	},
	select : function(event, ui) {
		$("#checkin_form_banner_id").val(ui.item.banner_id);
		return false; // NB: Must return false or default behavior will clear
						// the search field
	}
}).data("autocomplete")._renderItem = function(ul, item) {
	//
	var regExp = new RegExp("^" + this.term);
	var nameHighlight = item.name.replace(regExp,
			"<span style='font-weight:bold;'>" + this.term + "</span>");
	var userHighlight = item.username.replace(regExp,
			"<span style='font-weight:bold;'>" + this.term + "</span>");
	var bannerHighlight = item.banner_id.replace(regExp,
			"<span style='font-weight:bold;'>" + this.term + "</span>");
	// Custom HTML for the drop-down menu
	return $("<li>").data("item.autocomplete", item).append(
			"<a><span style='font-size:16px'>" + nameHighlight + "</span><br>"
					+ bannerHighlight + " &bull; " + userHighlight + "</a>")
			.appendTo(ul);
};
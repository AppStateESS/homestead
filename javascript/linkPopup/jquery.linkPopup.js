(function($) {
	$.fn.linkPopup = function(options) {
		return this.each(function() {
			$this = $(this);
			$this.bind('click', {link:$this}, function(e) {
				link = e.data.link;
				$("#linkPopupDialog").empty();
				$("#linkPopupDialog").load(link.attr('href'));
				$("#linkPopupDialog").dialog('open');
				return false;
			});
		});
	}
})(jQuery);
<script type="text/javascript" src="mod/hms/bower_components/typeahead.js/dist/typeahead.bundle.js"></script>
<script type="text/javascript">

$(function() {
	// Suggestion provider for server-provided results
	var studentSearchSource = new Bloodhound({
		name: 'remoteSearch',
	    datumTokenizer: function(datum){
	    	var nameTokens 		= Bloodhound.tokenizers.obj.whitespace('name');
	    	var bannerTokens 	= Bloodhound.tokenizers.obj.whitespace('banner_id');
	    	var usernameTokens  = Bloodhound.tokenizers.obj.whitespace('username');
	    	
	    	return nameTokens.concat(bannerTokens).concat(usernameToekns);
	    },
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: 'index.php?module=hms&action=AjaxGetUsernameSuggestions&studentSearchQuery=%QUERY',
		limit: 5
	});
	
	studentSearchSource.initialize();
	
	// Suggestion provider for recent searches
	var previousSearchSource = new Bloodhound({
		name: 'previousSearch',
		limit: 5,
		local: function (){
			local = localStorage.getItem('recentSearches');
			if(local == null){
				return [];
			}else{
				return JSON.parse(local);
			}
		},
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
	});
	
	previousSearchSource.initialize();
	
	// Initialize typeahead
	// TODO: add empty (no suggestions) template
	$('#studentSearch.typeahead').typeahead({
		highlight: true,
		hint: true,
		minLength: 0
	},
	{
		name: 'studentSearch',
		displayKey: 'banner_id',
		source: studentSearchSource.ttAdapter(),
		templates: {
			suggestion: function(suggestion) {
				return('<p>' + suggestion.name + "<br />" + suggestion.banner_id + " &bull; " + suggestion.username + "</p>");
			}
		}
	},
	{
		name: 'previousSearch',
		displayKey: 'banner_id',
		source: previousSearchSource.ttAdapter(),
		templates: {
			suggestion: function(suggestion) {
				return('<p>' + suggestion.name + "<br />" + suggestion.banner_id + " &bull; " + suggestion.username + "</p>");
			},
			header: function() {
				return ('<h5 style="margin: 0 20px 5px 20px; padding: 3px 0; border-bottom: 1px solid #ccc;">Previous Searches</h5>');
			}
		}
	}
	);
	
	// Event handler for selecting a suggestion
	$('#studentSearch').bind('typeahead:selected', function(obj, datum, name) {
		var local = localStorage.getItem('recentSearches');
		if(local == null){
			localStorage.setItem('recentSearches', JSON.stringify([datum]));
		} else {
			var searchList = JSON.parse(local);
			searchList.unshift(datum);
			localStorage.setItem('recentSearches', JSON.stringify(searchList));
		}
		
		location.href = 'index.php?module=hms&action=StudentSearch&banner_id=' + datum.banner_id;
	});
	
	// If the search bar gains focus, and there's nothing entered in the text box,
	// then trigger the typeahead to be shown (so we can show previous searches)
	$('#studentSearch').on( 'focus', function() {
		$('#studentSearch').typeahead('open');
	    //if($(this).val() === '') // you can also check for minLength
	        //$(this).data().ttTypeahead.input.trigger('typeahead:opened', '');
	});
	
	// TODO:
	// * Recent searches should show *all* results, not just ones that match
	// * Add event handler to capture return, and submit form even if no suggestion was selected
	// * Add a search icon that submits the form when clicked.
	// * Add spinner to let the user know the search is in progress
	// * Add 'empty' template, to show something if no suggestions found.
});
</script
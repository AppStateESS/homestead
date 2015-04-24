<script type="text/javascript" src="mod/hms/bower_components/typeahead.js/dist/typeahead.bundle.js"></script>
<script type="text/javascript">

$(function() {
<<<<<<< HEAD
	
	// If our local storage key for recent searches is empty, then initialize it with an empty array
	if(localStorage.getItem('recentSearches') == null) {
		localStorage.setItem('recentSearches', JSON.stringify([]));
	}
	
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
		remote: {
			url: 'index.php?module=hms&action=AjaxGetUsernameSuggestions&studentSearchQuery=%QUERY',
			wildcard: '%QUERY'
		}
	});
	
	// Suggestion provider for recent searches
	function previousSearchProvider(q, sync) {
		// Return an empty set if the query is not empty
		// This prevents recent search results from showing up after
		// the user types anything in the typeahead input
		if (q != ''){
			return [];
		}

		// Parse the JSON from local storage and pass it into 'sync' for the typeahead
		sync(JSON.parse(localStorage.getItem('recentSearches')));
	}
	
	// Initialize typeahead
	$('#studentSearch.typeahead').typeahead({
		highlight: true,
		hint: true,
		minLength: 0
	},
	{
		name: 'studentSearch',
		display: 'name',
		limit: 5,
		source: studentSearchSource.ttAdapter(),
		templates: {
			suggestion: function(suggestion) {
				return('<p>' + suggestion.name + "<br />" + suggestion.banner_id + " &bull; " + suggestion.username + "</p>");
			}
		}
	},
	{
		name: 'previousSearch',
		display: 'name',
		limit: 5,
		async: false,
		source: previousSearchProvider,
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
	$('#studentSearch').bind('typeahead:select', function(obj, datum, name) {
		// Grab the json encoded array from local storage
		var local = localStorage.getItem('recentSearches');
		
		// Parse the json into an array
		var searchList = JSON.parse(local);
		
		// Search for an existing copy of this datum, based on banner_id filed
		var existing = $.grep(searchList, function(item) { return item.banner_id === datum.banner_id});
		
		// If there were no matches (i.e. this suggestion isn't already stored), then store it
		if(existing.length == 0){
			// Shift the datum onto the beginning of the array
			searchList.unshift(datum);
			
			// JSON encode the arry and store it in local storage
			localStorage.setItem('recentSearches', JSON.stringify(searchList));
		}
		
		// Redirect to the student profile the user selected
		location.href = 'index.php?module=hms&action=StudentSearch&banner_id=' + datum.banner_id;
	});
	
	// Event handler for enter key.. Search with whatever the person put in the box
	$("#studentSearch").keyup(function(e){
		if(e.keyCode == 13) {
			// Redirect to the student profile the user selected
			location.href = 'index.php?module=hms&action=StudentSearch&banner_id=' + $("#studentSearch").val(); 
		}
	});
	
	// TODO:
	// * Add a search icon that submits the form when clicked.
	// * Add spinner to let the user know the search is in progress
	// * Add 'empty' template, to show something if no suggestions found.
});
</script

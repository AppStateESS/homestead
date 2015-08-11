/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(function() {

    $("#student-search-spinner").hide(); // Hide spinner right away

    // If our local storage key for recent searches is empty, then initialize it with an empty array
    if(localStorage.getItem('recentSearches') == null) {
        localStorage.setItem('recentSearches', JSON.stringify([]));
    }

    // Suggestion provider for server-provided results
    var studentSearchSource = new Bloodhound({
        name: 'remoteSearch',
        datumTokenizer: function(datum){
            var nameTokens      = Bloodhound.tokenizers.obj.whitespace('name');
            var bannerTokens    = Bloodhound.tokenizers.obj.whitespace('banner_id');
            var usernameTokens  = Bloodhound.tokenizers.obj.whitespace('username');

            return nameTokens.concat(bannerTokens).concat(usernameToekns);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: 'index.php?module=hms&action=AjaxGetUsernameSuggestions&studentSearchQuery=%QUERY',
            wildcard: '%QUERY',
            rateLimitWait: 1000,
            rateLimitBy: 'throttle'
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
            },
            empty: function() {
                return('<p style="margin: 0 20px 5px 20px; padding: 3px 0;" class="text-muted">No results found.</p>');
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

    // Stores a datum in the localStorage area, used later to create the previous results list
    var storeLocalDatum = function(datum)
    {
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
    }

    // Event handler for selecting a suggestion
    $('#studentSearch').bind('typeahead:select', function(obj, datum, name) {
        storeLocalDatum(datum);

        // Redirect to the student profile the user selected
        location.href = 'index.php?module=hms&action=StudentSearch&banner_id=' + datum.banner_id;
    });

    // Even handler for showing the "loading" spinner when a request is sent
    $('#studentSearch').bind('typeahead:asyncrequest', function(obj, datum, name) {
        $("#student-search-spinner").show();
    });

    // Even handler for hiding the "loading" spinner when a request is complete or cancelled
    $('#studentSearch').bind('typeahead:asyncreceive', function(obj, datum, name) {
        $("#student-search-spinner").hide();
    });

    // Event handler for enter key.. Search with whatever the person put in the box
    $("#studentSearch").keyup(function(e){
        // If they key pressed was anything other than the enter key, then return
        if(e.keyCode != 13) {
            return;
        }

        // Force a search for whatever's in the search box
        studentSearchSource.search($("#studentSearch").val(), saveVal, saveVal);

        // Callback for search completion. If any matches, save them
        function saveVal(datums)
        {
            if(datums.length == 1){
                storeLocalDatum(datums[0]);

                // Redirect to the student profile the user selected
                // NB: this is inside this if statement to prevent the browser from leaving the page before the result is saved
                location.href = 'index.php?module=hms&action=StudentSearch&banner_id=' + $("#studentSearch").val();
            } else {
                location.href = 'index.php?module=hms&action=StudentSearch&banner_id=' + $("#studentSearch").val();
            }
        }
    });
});

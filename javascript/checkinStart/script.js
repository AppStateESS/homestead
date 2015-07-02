$().ready(function() {
    loadSelect2();
    // Check if a hall has been saved in a cookie
    var hallId = $.cookie("hms-checkin-hall-id");
    var hallName = $.cookie("hms-checkin-hall-name");
    if (hallId !== null) {
        // Use the hall from the cookie
        selectHall(hallId, hallName);
    } else {
        // Setup the hall selector

        // Hide the hall name div
        $('#hallDiv').hide();

        // Hide the search box until a hall is selected
        $('#searchBoxDiv').hide();

        // Show the hall selector
        //$('#hallSelector').show();

        // Set onChange event handler for drop down box
        $('#checkin_form_residence_hall').bind('change', handleSelectHall);
        $('#changeLink').hide();
    }

    // Setup the card reader
    var cardReader = new CardReader();
    cardReader.observe(document);

    cardReader.validate(function(value) {
        // Tests if value is not equal to 'E'.
        if (value == 'E') {
            return false;
        }

        // Tests if value is not equal to 'E+E'.
        if (value == 'E+E') {
            return false;
        }

        if (value == '+E?') {
            return false;
        }

        if (value.indexOf('E') >= 0) {
            return false;
        }

        return true;
    });

    // Event handler for card read errors
    cardReader.cardError(function() {
        $("#cardswipe-error").show();
        $("#cardswipe-error").delay(2500).fadeOut('fast');
    });

    cardReader.cardRead(function(value) {
        //console.log(value);
        var bannerParts = value.split("=");
        $('#checkin_form_banner_id').val(bannerParts[0]);
        $('#checkin_form').submit();
    });

    $("#student-search-spinner").hide(); // Hide spinner right away

    // Suggestion provider for server-provided results
    var studentSearchSource = new Bloodhound({
        name: 'remoteSearch',
        datumTokenizer: function(datum) {
            var nameTokens = Bloodhound.tokenizers.obj.whitespace('name');
            var bannerTokens = Bloodhound.tokenizers.obj.whitespace('banner_id');
            var usernameTokens = Bloodhound.tokenizers.obj.whitespace('username');

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

    $('#checkin_form_banner_id.typeahead').typeahead({
        highlight: true,
        hint: true
                //minLength: 0
    },
    {
        name: 'studentSearch',
        display: 'banner_id',
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
    });
});

function loadSelect2()
{
    $("#checkin_form_residence_hall").select2();
}

function handleSelectHall()
{
    // Hide the drop down
    $('#hallSelector').hide('fast');

    // Get the select hall name and ID
    var hallId = $('#checkin_form_residence_hall').val();
    var hallName = $('#checkin_form_residence_hall option:selected').text();

    // Save the selection to a cookie
    $.cookie("hms-checkin-hall-id", hallId, 180);
    $.cookie("hms-checkin-hall-name", hallName, 180);

    selectHall(hallId, hallName);
}

function selectHall(hallId, hallName)
{
    // Hide the drop down
    $('#hallSelector').hide();

    // Set the name of the hall in the title
    $('#hallName').html(' ' + hallName);

    // Save the hall id to a hidden field
    $('#checkin_form_residence_hall_hidden').val(hallId);

    // Show the hall name div and change link
    $('#hallDiv').show();

    // Show the search box
    $('#searchBoxDiv').show();

    // Show the check in/out submit button
    $('#checkInButtonDiv').show();

    $('#changeLink').show();

    // Set the event handler for the 'change hall' link
    $('#changeLink').bind('click', changeHallLink);

    // Set focus to the banner id text box
    $('#checkin_form_banner_id').focus();
}

function changeHallLink()
{
    // Hide the hall name div
    $('#hallDiv').hide();

    // Hide the search box until a hall is selected
    $('#searchBoxDiv').hide();

    // Hide the check in/out submit button until a hall is selected
    $('#checkInButtonDiv').hide();

    // Show the hall selector
    $('#hallSelector').show('fast');

    // Set the dropdown box to have the default ("Select a hall..") option selected to avoid onChange bugs
    $("#hallSelector option[value='0']").attr("selected", "selected");

    // Set onChange event handler for drop down box
    $('#checkin_form_residence_hall').bind('change', handleSelectHall);
    return false;
}
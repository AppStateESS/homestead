// document ready
$(function() {

  // capture all enter and do nothing
  $('#phpws_form_emergency_contact_email').keypress(function(e) {
    if(e.which == 13) {
      $('#phpws_form_emergency_contact_email').trigger('focusout');
      return false;
    }
  });

  // attach jquery plugin to validate address
  $('#phpws_form_emergency_contact_email').mailgun_validator({
    api_key: 'pubkey-d296f8e565badf7cd16c0e19dc0041e9', // replace this with your Mailgun public API key
    in_progress: contact_validation_in_progress,
    success: contact_validation_success,
    error: contact_validation_error,
  });

  // capture all enter and do nothing
  $('#phpws_form_missing_person_email').keypress(function(e) {
    if(e.which == 13) {
      $('#phpws_form_missing_person_email').trigger('focusout');
      return false;
    }
  });

  // attach jquery plugin to validate address
  $('#phpws_form_missing_person_email').mailgun_validator({
    api_key: 'pubkey-d296f8e565badf7cd16c0e19dc0041e9', // replace this with your Mailgun public API key
    in_progress: missing_validation_in_progress,
    success: missing_validation_success,
    error: missing_validation_error,
  });

});

// while the lookup is performing
function contact_validation_in_progress() {
  $('#contact_status').html("<img src='loading.gif' height='16'/>");
}

// if email successfull validated
function contact_validation_success(data) {
  $('#contact_status').html(get_suggestion_str(data['is_valid'], data['did_you_mean']));
}

// if email is invalid
function contact_validation_error(error_message) {
  $('#contact_status').html(error_message);
}

// while the lookup is performing
function missing_validation_in_progress() {
  $('#missing_status').html("<img src='loading.gif' height='16'/>");
}

// if email successfull validated
function missing_validation_success(data) {
  $('#missing_status').html(get_suggestion_str(data['is_valid'], data['did_you_mean']));
}

// if email is invalid
function missing_validation_error(error_message) {
  $('#missing_status').html(error_message);
}



// suggest a valid email
function get_suggestion_str(is_valid, alternate) {
  if (is_valid) {
    var result = '<p class="text-success">Address is valid.</p>';
    if (alternate) {
      result += '<p class="text-warning"> (Though did you mean <em>' + alternate + '</em>?)</p>';
    }
    return result
  } else if (alternate) {
    return '<p class="text-warning">Did you mean <em>' +  alternate + '</em>?</p>';
  } else {
    return '<p class="text-danger">Address is invalid.</p>';
  }
}

<script>
function CountWords (this_field)
{
    var fullStr = this_field.prop('value') + " ";
    var left_trimmedStr = fullStr.replace(/^[^A-Za-z0-9]+/gi, "");
    var cleanedStr = left_trimmedStr.replace(/[^A-Za-z0-9]+/gi, " ");
    var splitString = cleanedStr.split(" ");
    var word_count = splitString.length - 1;
    var words_left = 500 - word_count;
    if (fullStr.length <1) {
        word_count = 0;
    }

    if (words_left == 1)
    {
        wordOrWords = " word ";
    } else {
        wordOrWords = " words ";
    }

    // Convert integer number of words remaining to a string
    str_words_left = String(words_left)
    str_words_left = str_words_left + wordOrWords + "remaining"

    // If at or over limit, highlight in red
    if (words_left < 0)
    {
        str_words_left = "<span class='text-danger'>" + str_words_left + "</span>";
    }

    // Update the help text span with the formatted string
    this_field.siblings(".help-block").html(str_words_left);
}

$().ready(function (){
	CountWords($("#phpws_form_why_specific_communities"));
	$("#phpws_form_why_specific_communities").on('change keyup paste', function(){
		CountWords($("#phpws_form_why_specific_communities"));
	});

    CountWords($("#phpws_form_strengths_weaknesses"));
	$("#phpws_form_strengths_weaknesses").on('change keyup paste', function(){
		CountWords($("#phpws_form_strengths_weaknesses"));
	});
});
</script>
<h1>Residential Learning Community Application</h1>

<p>Residential Learning Communities (RLCs) are a unique housing opportunity.  Ranked as a 2010 Best College for Learning Communities by U.S. News & World Report, Appalachian's RLCs offer great experiences for all community members.  In addition, research shows students who participate in Residential Learning Communities have a higher GPA and enjoy a better college experience.  One of the best ways to develop strong friendships and succeed in college is to join a Residential Learning Community!</p>

<p class="lead">To learn more about our RLC options, visit the <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Communities website</a>.</p>
<hr>

<div class="row">
    <div class="col-md-7">
        <h2>1. Rank Your Community Choices</h2>
        <p>Choose up to three communities you'd like us to consider your application for.</p>
    </div>
</div>
{START_FORM}
<div class="row">
    <div class="col-md-5 col-md-push-7">
        <div class="alert alert-info">
            <h4><i class="fa fa-exclamation"></i> Interested in The Honors College or Watauga Global Community?</h4>
            <p>You must apply directly to these two communities through their separate application processes.  See the <a href="http://www.honors.appstate.edu/" class="alert-link" target="_blank">The Honors College</a> or <a href="http://wataugaglobal.appstate.edu/" class="alert-link" target="_blank">Watauga Global Community</a> websites for details.</p>
        </div>

        <div class="alert alert-info">
            <h4><i class="fa fa-exclamation"></i> A Note About Roommates</h4>
            <p> You cannot be accepted into a Residential Learning Community if you have requested a roommate who does not also apply to the same community.  In addition, once invited to a community, you can no longer request roommates who have not applied to the same community.</p>
        </div>
    </div>

    <div class="col-md-7 col-md-pull-5">

        <div class="form-group">
            {RLC_FIRST_CHOICE_LABEL}
            {RLC_FIRST_CHOICE}
        </div>

        <div class="form-group">
            {RLC_SECOND_CHOICE_LABEL}
            {RLC_SECOND_CHOICE}
        </div>

        <div class="form-group">
            {RLC_THIRD_CHOICE_LABEL}
            {RLC_THIRD_CHOICE}
        </div>

        <h2>2. About Your Choices</h2>
        <div class="form-group">
            {WHY_SPECIFIC_COMMUNITIES_LABEL}
            <span class="help-block"></span>
            <textarea name="why_specific_communities" id="phpws_form_why_specific_communities" title="Why are you interested in the specific communities you have chosen?" rows="15" class="form-control"></textarea>
        </div>

        <div class="form-group">
            {STRENGTHS_WEAKNESSES_LABEL}
            <span class="help-block"></span>
            <textarea name="strengths_weaknesses" id="phpws_form_strengths_weaknesses" title="What are your strengths and in what areas would you like to improve?" rows="15" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <a href="index.php" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-success btn-lg pull-right">Continue <i class="fa fa-chevron-right"></i></button>
        </div>
    </div>
</div>

{END_FORM}

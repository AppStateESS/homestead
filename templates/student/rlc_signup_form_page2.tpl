<h1>Residential Learning Community Application</h1>

{START_FORM}

<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            {RLC_QUESTION_0_LABEL}
            <span class="help-block"></span>
            {RLC_QUESTION_0}
        </div>

        <div class="form-group">
            {RLC_QUESTION_1_LABEL}
            <span class="help-block"></span>
            {RLC_QUESTION_1}
        </div>

        <div class="form-group">
            {RLC_QUESTION_2_LABEL}
            <span class="help-block"></span>
            {RLC_QUESTION_2}
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success btn-lg pull-right">
              Submit Application
            </button>
            <a href="index.php" class="btn btn-danger btn-lg">
              <i class="fa fa-chevron-left"></i>
              Cancel
            </a>
        </div>
    </div>
</div>

{END_FORM}

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
	CountWords($("#phpws_form_rlc_question_0"));
	$("#phpws_form_rlc_question_0").on('change keyup paste', function(){
		CountWords($("#phpws_form_rlc_question_0"));
	});

    CountWords($("#phpws_form_rlc_question_1"));
    $("#phpws_form_rlc_question_1").on('change keyup paste', function(){
		CountWords1($("#phpws_form_rlc_question_1"));
	});

    CountWords($("#phpws_form_rlc_question_2"));
	$("#phpws_form_rlc_question_2").on('change keyup paste', function(){
		CountWords2($("#phpws_form_rlc_question_2"));
	});
});

</script>

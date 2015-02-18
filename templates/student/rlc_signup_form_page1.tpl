<script>
function CountWordsSpecific (this_field) 
{
      var char_count = this_field.prop('value').length;
      var fullStr = this_field.prop('value') + " ";
      var initial_whitespace_rExp = /^[^A-Za-z0-9]+/gi;
      var left_trimmedStr = fullStr.replace(initial_whitespace_rExp, "");
      var non_alphanumerics_rExp = rExp = /[^A-Za-z0-9]+/gi;
      var cleanedStr = left_trimmedStr.replace(non_alphanumerics_rExp, " ");
      var splitString = cleanedStr.split(" ");
      var word_count = splitString.length -1;
      var words_left = 500 - (splitString.length - 1);
      if (fullStr.length <1) {
            word_count = 0;
      }
      if (words_left == 1)
      {
      	wordOrWords = " word ";
      }
      else 
      {
      	wordOrWords = " words ";
      }
      str_words_left = String(words_left)

      if (words_left < 0)
      {
      	var formatted = "<span style='color:#ff0000'>" + str_words_left + "</span>";
      }
      else
      {
      	var formatted = str_words_left;
      }
      var retstring = formatted + wordOrWords + "remaining."
      document.getElementById('specific').innerHTML=retstring;
      
}
$().ready(function (){
	CountWordsSpecific($("#phpws_form_why_specific_communities"));
	$("#phpws_form_why_specific_communities").keydown(function(){
		CountWordsSpecific($("#phpws_form_why_specific_communities"));
	});
});

function CountWordsStrengths (this_field) 
{
      var char_count = this_field.prop('value').length;
      var fullStr = this_field.prop('value') + " ";
      var initial_whitespace_rExp = /^[^A-Za-z0-9]+/gi;
      var left_trimmedStr = fullStr.replace(initial_whitespace_rExp, "");
      var non_alphanumerics_rExp = rExp = /[^A-Za-z0-9]+/gi;
      var cleanedStr = left_trimmedStr.replace(non_alphanumerics_rExp, " ");
      var splitString = cleanedStr.split(" ");
      var word_count = splitString.length -1;
      var words_left = 500 - (splitString.length - 1);
      if (fullStr.length <1) {
            word_count = 0;
      }
      if (words_left == 1)
      {
      	wordOrWords = " word ";
      }
      else 
      {
      	wordOrWords = " words ";
      }
      str_words_left = String(words_left)

      if (words_left < 0)
      {
      	var formatted = "<span style='color:#ff0000'>" + str_words_left + "</span>";
      }
      else
      {
      	var formatted = str_words_left;
      }
      var retstring = formatted + wordOrWords + "remaining."
      document.getElementById('strengths').innerHTML=retstring;
      
}
$().ready(function (){
	CountWordsStrengths($("#phpws_form_strengths_weaknesses"));
	$("#phpws_form_strengths_weaknesses").keydown(function(){
		CountWordsStrengths($("#phpws_form_strengths_weaknesses"));
	});
});
</script>

<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>Residential Learning Community Application</h1> </div>
    <div class="box-content">
    
    <p>This is an additional application for Residential Learning Communities. To learn more about the different options available to you please visit the <a href="http://housing.appstate.edu/rlc" target="_blank">Residential Learning Communities website</a>.</p> 

    <p>If you are interested in The Honors College or Watauga Global Community please note your interest below.  In addition to this application, you must also submit a separate application for these communities.  Please apply for membership to any of these programs -- <a href="http://www.honors.appstate.edu/" target="_blank">The Honors College</a> or <a href="http://wataugaglobal.appstate.edu/" target="_blank">Watauga Global Community</a> -- on their websites.</p> 

    <p style="border: 1px solid red; padding: 3px; background: #F5F5F5"><strong>Note:</strong> You cannot be accepted into a learning community with a pre-chosen roommate who does not apply to the same community.  In addition, once you apply, you can no longer choose roommates who have not applied to the same community.</p>
    
        <!-- BEGIN rlc_form -->
        {START_FORM}
        <table>
            <tr>
                <th colspan="2">1. About You</th>
            </tr>
            <tr>
                <td>{APPLENET_USERNAME_LABEL}</td>
                <td>{APPLENET_USERNAME}</td>
            </tr>
            <tr>
                <td>{NAME_LABEL}</td>
                <td>{NAME}</td>
            </tr>
            <tr>
                <th colspan="2">2. Rank Your Community Choices</th>
            </tr>
            <tr>
                <td>{RLC_FIRST_CHOICE_LABEL}</td>
                <td>{RLC_FIRST_CHOICE}</td>
            </tr>
            <tr>
                <td>{RLC_SECOND_CHOICE_LABEL}</td>
                <td>{RLC_SECOND_CHOICE}</td>
            </tr>
            <tr>
                <td>{RLC_THIRD_CHOICE_LABEL}</td>
                <td>{RLC_THIRD_CHOICE}</td>
            </tr>
            <tr>
                <th colspan="2">3. About Your Choices</th>
            </tr>
            <tr>
                <td>{WHY_SPECIFIC_COMMUNITIES_LABEL}</td>
                <td><div id="specific"></div>{WHY_SPECIFIC_COMMUNITIES}</td>
            </tr>
            <tr>
                <td>{STRENGTHS_WEAKNESSES_LABEL}</td>
                <td><div id="strengths"></div>{STRENGTHS_WEAKNESSES}</td>
            </tr>
            <tr>
        <tr>
            <td colspan="2" align="left">{SUBMIT} {CANCEL}</td>
        </tr> 
        </table>
        {END_FORM}
        <!-- END rlc_form -->
      </div>
   </div>
</div>

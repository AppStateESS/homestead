<script>

function CountWords0 (this_field) 
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
      document.getElementById('question0').innerHTML=retstring;
      
}
$().ready(function (){
	CountWords0($("#phpws_form_rlc_question_0"));
	$("#phpws_form_rlc_question_0").keydown(function(){
		CountWords0($("#phpws_form_rlc_question_0"));
	});
});


function CountWords1 (this_field) 
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
      document.getElementById('question1').innerHTML=retstring;
      
}
$().ready(function (){
	CountWords1($("#phpws_form_rlc_question_1"));
	$("#phpws_form_rlc_question_1").keydown(function(){
		CountWords1($("#phpws_form_rlc_question_1"));
	});
});

function CountWords2 (this_field) 
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
      document.getElementById('question2').innerHTML=retstring;
      
}
$().ready(function (){
	CountWords2($("#phpws_form_rlc_question_2"));
	$("#phpws_form_rlc_question_2").keydown(function(){
		CountWords2($("#phpws_form_rlc_question_2"));
	});
});
</script>

<div class="hms">
  <div class="box">
    <div class="box-title"><h1>Residential Learning Community Application</h1></div>
    <div class="box-content">
        <font color="red"><i>{MESSAGE}</i></font><br>
        <!-- BEGIN rlc_form2 -->
        {START_FORM}
        <table>
            <tr>
                <td>{RLC_QUESTION_0_LABEL}</td>
                <td><div id="question0"></div>{RLC_QUESTION_0}</td>
            </tr>
            <tr>
                <td>{RLC_QUESTION_1_LABEL}</td>
                <td><div id="question1"></div>{RLC_QUESTION_1}</td>
            <tr>
                <td>{RLC_QUESTION_2_LABEL}</td>
                <td><div id="question2"></div>{RLC_QUESTION_2}</td>
            </tr>
            <tr>
               <td colspan="2" align="left">{SUBMIT} {CANCEL}</td>
           </tr> 
        </table>
        {END_FORM}
        <!-- END rlc_form2 -->
    </div>
  </div>
</div>

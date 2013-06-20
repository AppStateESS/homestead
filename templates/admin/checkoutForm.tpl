<div style="margin:auto; text-align:center">
  <h1>{NAME} <span style="color: #CCC">({BANNER_ID})</span></h1>
  <h3>checking out of</h3>
  <h1>{ASSIGNMENT}</h1>
</div>

{START_FORM}
<hr />
{KEY_CODE_LABEL}:&nbsp;{KEY_CODE}<br />
{KEY_NOT_RETURNED}&nbsp; {KEY_NOT_RETURNED_LABEL}
<hr />

<h3>Room Damages</h3>
<ul>
  <!-- BEGIN damages_repeat -->
  <li>{REPORTED_ON} &bullet; {CATEGORY} - {DESCRIPTION} &bullet; {SIDE} &bullet; {NOTE}</li>
  <!-- END damages_repeat -->
</ul>

<a href="#">{ADD_DAMAGE_LINK}</a>

<hr />
{IMPROPER_CHECKOUT}{IMPROPER_CHECKOUT_LABEL}
<hr />

<br />
{SUBMIT}
{END_FORM}

<div id="addDamageDialog">

</div>
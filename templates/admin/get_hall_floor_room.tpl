{START_FORM}
<div class="hms">
  <div class="box">
    <div class="box-title"> <h1>{TITLE}</h1> </div>
    <div class="box-content">
        <font color="red"><i>{ERROR}</i><br /></font>
        {MESSAGE}<br />
        <table>
            <tr>
                <th align="left">Building:&nbsp;&nbsp;&nbsp;&nbsp;</th><td>{HALLS}</td>
            </tr>
            <tr>
                <th align="left">Floor:</th><td>{FLOORS}</td>
            </tr>
            <tr>
                <th align="left">Room:</th><td>{ROOMS}</td>
            </tr>
            <tr>
                <th align="left">Bedroom:</th><td>{BEDROOM_LETTER}</td>
            </tr>
            <tr>
                <th align="left">Bed: </th><td>{BED_LETTER}</td>
            </tr>
<!-- BEGIN meals -->
            <tr>
                <th align="left">Meal Plan: </th><td>{MEAL_OPTION}</td>
            </tr>
<!-- END meals -->
        </table>
        <br />
        {SUBMIT}
    </div>
  </div>
</div>
{END_FORM}

<div class="hms">
  <div class="box">
    <div class="header"> <h1>Confirm Roommate Request</h1> </div>
    <div class="box-content">
        <p>
            {REQUESTOR} has requested you as a roommate in <b>{HALL_ROOM}</b>. The other assigned roommates, requested roommates, and vacant beds are listed below for your consideration. Please be aware that empty beds will be made available to other students.
        </p>
        <table>
            <tr>
                <th>Bedroom</th>
                <th>Roommate</th>
            </tr>
            <!-- BEGIN beds -->
            <tr>
                <td>{BEDROOM_LETTER}</td>
                <td>{TEXT}</td>
            </tr>
            <!-- END beds -->
        </table>

        {START_FORM}
        <p>Please choose a meal plan. <b>Note: </b>Most residence halls require you to choose a meal plan. If your chosen residence hall does not require a meal plan, then a 'None' option will be available in drop down box below.</p>
        <p>Meal plan: {MEAL_PLAN}</p>

        <p>
            To confirm your choices, please continue to the next page by clicking the continue button below.
        </p>
        {ACCEPT}
        {REJECT}
        {END_FORM}
    </div>
  </div>
</div>

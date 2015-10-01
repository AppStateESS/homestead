{START_FORM}

<h1>Assign Student <small>{TERM}</small></h1>

<div class="row">
    <div class="col-md-4">

        <label for="{USERNAME_ID}">ASU Email:</label>
        <div class="input-group">
            {USERNAME}
            <span class="input-group-addon">@appstate.edu</span>
        </div>

        <script>
          var prepopulate = {PREPOPULATE};
          var mealPlan = {MEAL_PLAN};
        </script>

        <div id="StudentAssigner">
        </div>

        <div class="form-group">
            <label for="">Note: </label>
            {NOTE}
        </div>

        <button type="submit" class="btn btn-success">Assign</button>

    </div>
</div>

{END_FORM}

<h2>Add/Edit a Learning Community</h2>

<!-- BEGIN community -->
<h3>{COMMUNITY}</h3>
<!-- END community -->

{START_FORM}

<div class="row">
    <div class="col-sm-6 col-lg-4">
        <h3>General Settings</h3>
        <div class='form-group'>
            <label for="add_learning_community_community_name">Community Name</label>
            {COMMUNITY_NAME}
        </div>
        <div class='form-group'>
            <label for="add_learning_community_abbreviation">Abbreviation</label>
            {ABBREVIATION}
        </div>
        <div class='form-group'>
            <label for="add_learning_community_capacity">Capacity</label>
            {CAPACITY}
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <h3>Move-in Times</h3>
        <div class='form-group'>
            <label for="add_learning_community_f_movein_time">{F_MOVEIN_TIME_LABEL}</label>
            {F_MOVEIN_TIME}
        </div>
        <div class='form-group'>
            <label for="add_learning_community_t_movein_time">{T_MOVEIN_TIME_LABEL}</label>
            {T_MOVEIN_TIME}
        </div>
        <div class='form-group'>
            <label for="add_learning_community_c_movein_time">{C_MOVEIN_TIME_LABEL}</label>
            {C_MOVEIN_TIME}
        </div>
    </div>
    <div class="col-sm-12 col-lg-4">
        <h3>Student Types Allowed</h3>
        <div class='form-group'>
            <label>First-time Application Allowed Student Types</label> <small>(comma separated list, i.e.: 'F,C,T')</small>
            {STUDENT_TYPES}
        </div>
        <div class='form-group'>
            <label>Re-application Allowed Student Types</label> <small>(comma separated list, i.e.: 'F,C,T')</small>
            {REAPPLICATION_STUDENT_TYPES} 
        </div>
        <div class='form-group'>
            {MEMBERS_REAPPLY} {MEMBERS_REAPPLY_LABEL}
        </div>
    </div>
</div>



<h3>Application Questions</h3>
<div class='form-group'>
    {FRESHMEN_QUESTION_LABEL}
    {FRESHMEN_QUESTION}
</div>
<div class='form-group'>
    {RETURNING_QUESTION_LABEL}
    {RETURNING_QUESTION}
</div>

<h3>Terms &amp; Conditions</h3>
<p>This text will be included in the invitation email sent to accepted students.</p>
{TERMS_CONDITIONS}

{SUBMIT} {END_FORM}

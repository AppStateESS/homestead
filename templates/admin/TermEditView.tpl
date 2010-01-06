<div class="hms">
    <div class="box">
        <div class="box-title"><h1>{TITLE}</h1></div>
        <fieldset><legend>{CURRENT_TERM_LEGEND}</legend>
            <p>{CURRENT_TERM_TEXT}</p>
            <!-- BEGIN CURTERM_LINK --><p>{CURRENT_TERM_LINK}</p><!--  END CURTERM_LINK -->
        </fieldset>
        <fieldset><legend>{BANNER_QUEUE_LEGEND}</legend>
            <p>{BANNER_QUEUE_TEXT}<!-- BEGIN BQ_LINK -->&nbsp;&nbsp;[{BANNER_QUEUE_LINK}]<!-- END BQ_LINK --></p>
            <!-- BEGIN BANNER_QUEUE_PROCESS --><p>{BANNER_QUEUE_COUNT}<!-- BEGIN BQP_LINK --> [{BANNER_QUEUE_PROCESS}]<!-- END BQP_LINK --></p><!-- END BANNER_QUEUE_PROCESS -->
        </fieldset>
        <fieldset><legend>{FEATURES_DEADLINES_LEGEND}</legend>
            {FEATURES_DEADLINES_CONTENT}
        </fieldset>
    </div>
</div>
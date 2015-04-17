<form class="navbar-form navbar-left" id="{FORM_ID}" method="{FORM_METHOD}" action="{FORM_ACTION}">
	{HIDDEN_FIELDS}
	<div class="form-group">
    	<select name="{TERM_NAME}" class="form-control" id="{TERM_ID}">
    		<!-- BEGIN TERM_OPTIONS -->
    		<option value="{id}" {selected}>{term}</option>
    		<!-- END TERM_OPTIONS -->
    	</select>
    </div>
</form>
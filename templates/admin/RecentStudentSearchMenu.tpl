<div id="recentSearches" class="rounded-box">
    <div class="boxheader">
        <h2 class="rrze-report-run-22">Recent Searches</h2>
    </div>

	<div id="recentSearchTabs">
		<ul>
			<li><a href="#recentSearchTabs-1">You</a>
			</li>
			<li><a href="#recentSearchTabs-2">Everyone</a>
			</li>
		</ul>
		<div id="recentSearchTabs-1">
		  {USER}
		</div>
		<div id="recentSearchTabs-2">
		  {GLOBAL}
		</div>
	</div>
</div>

<script>
	$(function() {
		$( "#recentSearchTabs" ).tabs();
	});
</script>
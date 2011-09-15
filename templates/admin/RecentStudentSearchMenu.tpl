<div id="recentSearches" class="rounded-box">
    <div class="boxheader emptyheader">&nbsp;</div>
	<div id="recentSearchesHeader">
		<strong><img src="mod/hms/img/tango/system-search.png">Recent Searches</strong>
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
<script type="text/javascript" src="mod/hms/bower_components/flot/jquery.flot.js"></script>
<script type="text/javascript" src="mod/hms/bower_components/flot/jquery.flot.time.js"></script>

<h1>{NAME} - {TERM}</h1>

Executed on: {EXEC_DATE} by {EXEC_USER}<br />

<div id="placeholder" style="width:600px; height: 300px"></div>

<script type="text/javascript">

$(function() {

    var d1 = {label: "{lastTerm}",
    		  data: {lastYearSeries}};

    var d2 = {label: "{thisTerm}",
    		  data: {thisYearSeries}};

    $.plot("#placeholder", [ d1, d2 ], {
    	xaxis: { mode: "time",
    		     timeformat: "%m/%d",
    		     minTickSize: [7, "day"],
    		     ticks: 15
    		     },
    	legend: { show: true,
    		      position: 'se'}
    });
});
</script>

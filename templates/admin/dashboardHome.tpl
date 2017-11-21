
<div class="row">
    <div class="col-lg-3 col-sm-6">
        <div class="card card-stats">
            <div class="content">
                <div class="row">
                    <div class="col-xs-5">
                        <div class="icon-big text-center icon-warning">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="col-xs-7">
                        <div class="numbers">
                            <p>Residents</p>
                            {NUM_RESIDENTS}
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-refresh"></i>
                        <!-- react-text: 125 --> <!-- /react-text --><!-- react-text: 126 -->Updated now<!-- /react-text -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="card card-stats">
            <div class="content">
                <div class="row">
                    <div class="col-xs-5">
                        <div class="icon-big text-center icon-warning">
                            <i class="fa fa-bed"></i>
                        </div>
                    </div>
                    <div class="col-xs-7">
                        <div class="numbers">
                            <p>Available Beds</p>
                            {NUM_BEDS_AVAIL}
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-refresh"></i>
                        <!-- react-text: 125 --> <!-- /react-text --><!-- react-text: 126 -->Updated now<!-- /react-text -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="card card-stats">
            <div class="content">
                <div class="row">
                    <div class="col-xs-5">
                        <div class="icon-big text-center icon-warning">
                            <i class="fa fa-exclamation"></i>
                        </div>
                    </div>
                    <div class="col-xs-7">
                        <div class="numbers">
                            <p>Overflow Assignments</p>
                            {OVERFLOW_ASSIGNMENTS}
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-refresh"></i>
                        <!-- react-text: 125 --> <!-- /react-text --><!-- react-text: 126 -->Updated now<!-- /react-text -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="header">
                <h4 class="title">Class Makeup</h4>
                <p class="category">Current Residents</p>
            </div>
            <div class="content">
                <div id="chartPreferences" class="ct-chart ct-perfect-fourth"></div>

                <div class="footer">
                    <div class="legend">
                        <i class="fa fa-circle text-info"></i> Freshmen
                        <i class="fa fa-circle text-danger"></i> Sophomore
                        <i class="fa fa-circle text-warning"></i> Junior
                        <i class="fa fa-circle text-success"></i> Senior
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="header">
                <h4 class="title">Residents Over Time</h4>
                <p class="category">Recent &amp; Future Semesters</p>
            </div>
            <div class="content">
                <div id="chartHours" class="ct-chart"></div>
                <div class="footer">
                    <div class="legend">
                        <i class="fa fa-circle text-info"></i> Fall 2017
                        <i class="fa fa-circle text-danger"></i> Spring 2018
                    </div>
                    <hr>
                    <div class="stats">
                        <i class="fa fa-history"></i> Updated 3 minutes ago
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card ">
            <div class="header">
                <h4 class="title">Applications</h4>
                <p class="category">Student data for the past year</p>
            </div>
            <div class="content">
                <div id="chartActivity" class="ct-chart"></div>

                <div class="footer">
                    <div class="legend">
                        <i class="fa fa-circle text-info"></i> New
                        <i class="fa fa-circle text-danger"></i> Returning
                    </div>
                    <hr>
                    <div class="stats">
                        <i class="fa fa-history"></i> Updated 3 minutes ago
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--  Charts Plugin -->
<script src="./mod/hms/node_modules/chartist/dist/chartist.min.js"></script>
<script src="./mod/hms/node_modules/moment/min/moment-with-locales.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){

        var dataAssignments = {
          series: [
              {
                name: 'Fall 2017',
                data: [
                	{x: new Date(1484697600000), y: 192},
                	{x: new Date(1484784000000), y: 275},
                	{x: new Date(1484870400000), y: 301},
                	{x: new Date(1484956800000), y: 313},
                	{x: new Date(1485043200000), y: 317},
                	{x: new Date(1485129600000), y: 325},
                	{x: new Date(1485216000000), y: 336},
                	{x: new Date(1485302400000), y: 342},
                	{x: new Date(1485388800000), y: 349},
                	{x: new Date(1485475200000), y: 353},
                	{x: new Date(1485561600000), y: 359},
                	{x: new Date(1485648000000), y: 363},
                	{x: new Date(1485734400000), y: 376},
                	{x: new Date(1485820800000), y: 386},
                	{x: new Date(1485907200000), y: 403},
                	{x: new Date(1485993600000), y: 405},
                	{x: new Date(1486080000000), y: 412},
                	{x: new Date(1486166400000), y: 412},
                	{x: new Date(1486252800000), y: 412},
                	{x: new Date(1486339200000), y: 412},
                	{x: new Date(1486425600000), y: 412},
                	{x: new Date(1486512000000), y: 436},
                	{x: new Date(1486598400000), y: 478},
                	{x: new Date(1486684800000), y: 510},
                	{x: new Date(1486771200000), y: 511},
                	{x: new Date(1486857600000), y: 511},
                	{x: new Date(1486944000000), y: 511},
                	{x: new Date(1487030400000), y: 870},
                	{x: new Date(1487116800000), y: 1035},
                	{x: new Date(1487203200000), y: 1193},
                	{x: new Date(1487289600000), y: 1584},
                	{x: new Date(1487376000000), y: 1627},
                	{x: new Date(1487462400000), y: 1641},
                	{x: new Date(1487548800000), y: 1641},
                	{x: new Date(1487635200000), y: 1793},
                	{x: new Date(1487721600000), y: 1935},
                	{x: new Date(1487808000000), y: 2006},
                	{x: new Date(1487894400000), y: 2069},
                	{x: new Date(1487980800000), y: 2114},
                	{x: new Date(1488067200000), y: 2126},
                	{x: new Date(1488153600000), y: 2129},
                	{x: new Date(1488240000000), y: 2134},
                	{x: new Date(1488326400000), y: 2137},
                	{x: new Date(1488412800000), y: 2137},
                	{x: new Date(1488499200000), y: 2137},
                	{x: new Date(1488585600000), y: 2150},
                	{x: new Date(1488672000000), y: 2150},
                	{x: new Date(1488758400000), y: 2150},
                	{x: new Date(1488844800000), y: 2151},
                	{x: new Date(1488931200000), y: 2149},
                	{x: new Date(1489017600000), y: 2168},
                	{x: new Date(1489104000000), y: 2171},
                	{x: new Date(1489190400000), y: 2232},
                	{x: new Date(1489276800000), y: 2232},
                	{x: new Date(1489363200000), y: 2232},
                	{x: new Date(1489449600000), y: 2232},
                	{x: new Date(1489536000000), y: 2260},
                	{x: new Date(1489622400000), y: 2264},
                	{x: new Date(1489708800000), y: 2292},
                	{x: new Date(1489795200000), y: 2294},
                	{x: new Date(1489881600000), y: 2294},
                	{x: new Date(1489968000000), y: 2294},
                	{x: new Date(1490054400000), y: 2296},
                	{x: new Date(1490140800000), y: 2294},
                	{x: new Date(1490227200000), y: 2293},
                	{x: new Date(1490313600000), y: 2292},
                	{x: new Date(1490400000000), y: 2351},
                	{x: new Date(1490486400000), y: 2351},
                	{x: new Date(1490572800000), y: 2351},
                	{x: new Date(1490659200000), y: 2376},
                	{x: new Date(1490745600000), y: 2409},
                	{x: new Date(1490832000000), y: 2412},
                	{x: new Date(1490918400000), y: 2416},
                	{x: new Date(1491004800000), y: 2437},
                	{x: new Date(1491091200000), y: 2437},
                	{x: new Date(1491177600000), y: 2437},
                	{x: new Date(1491264000000), y: 2437},
                	{x: new Date(1491350400000), y: 2437},
                	{x: new Date(1491436800000), y: 2437},
                	{x: new Date(1491523200000), y: 2444},
                	{x: new Date(1491609600000), y: 2500},
                	{x: new Date(1491696000000), y: 2500},
                	{x: new Date(1491782400000), y: 2500},
                	{x: new Date(1491868800000), y: 2502},
                	{x: new Date(1491955200000), y: 2503},
                	{x: new Date(1492041600000), y: 2520},
                	{x: new Date(1492128000000), y: 2520},
                	{x: new Date(1492214400000), y: 2515},
                	{x: new Date(1492300800000), y: 2515},
                	{x: new Date(1492387200000), y: 2515},
                	{x: new Date(1492473600000), y: 2515},
                	{x: new Date(1492560000000), y: 2515},
                	{x: new Date(1492646400000), y: 2514},
                	{x: new Date(1492732800000), y: 2513},
                	{x: new Date(1492819200000), y: 2511},
                	{x: new Date(1492905600000), y: 2511},
                	{x: new Date(1492992000000), y: 2511},
                	{x: new Date(1493078400000), y: 2509},
                	{x: new Date(1493164800000), y: 2511},
                	{x: new Date(1493251200000), y: 2510},
                	{x: new Date(1493337600000), y: 2511},
                	{x: new Date(1493424000000), y: 2511},
                	{x: new Date(1493510400000), y: 2511},
                	{x: new Date(1493596800000), y: 2511},
                	{x: new Date(1493683200000), y: 2543},
                	{x: new Date(1493769600000), y: 2541},
                	{x: new Date(1493856000000), y: 2542},
                	{x: new Date(1493942400000), y: 2553},
                	{x: new Date(1494028800000), y: 2556},
                	{x: new Date(1494115200000), y: 2556},
                	{x: new Date(1494201600000), y: 2556},
                	{x: new Date(1494288000000), y: 2552},
                	{x: new Date(1494374400000), y: 2554},
                	{x: new Date(1494460800000), y: 2568},
                	{x: new Date(1494547200000), y: 2588},
                	{x: new Date(1494633600000), y: 2587},
                	{x: new Date(1494720000000), y: 2587},
                	{x: new Date(1494806400000), y: 2587},
                	{x: new Date(1494892800000), y: 2579},
                	{x: new Date(1494979200000), y: 2583},
                	{x: new Date(1495065600000), y: 2582},
                	{x: new Date(1495152000000), y: 2582},
                	{x: new Date(1495238400000), y: 2589},
                	{x: new Date(1495324800000), y: 2589},
                	{x: new Date(1495411200000), y: 2590},
                	{x: new Date(1495497600000), y: 2588},
                	{x: new Date(1495584000000), y: 2588},
                	{x: new Date(1495670400000), y: 2587},
                	{x: new Date(1495756800000), y: 2587},
                	{x: new Date(1495843200000), y: 2581},
                	{x: new Date(1495929600000), y: 2581},
                	{x: new Date(1496016000000), y: 2581},
                	{x: new Date(1496102400000), y: 2661},
                	{x: new Date(1496188800000), y: 2659},
                	{x: new Date(1496275200000), y: 2730},
                	{x: new Date(1496361600000), y: 2728},
                	{x: new Date(1496448000000), y: 2714},
                	{x: new Date(1496534400000), y: 2714},
                	{x: new Date(1496620800000), y: 2714},
                	{x: new Date(1496707200000), y: 2735},
                	{x: new Date(1496793600000), y: 2778},
                	{x: new Date(1496880000000), y: 2783},
                	{x: new Date(1496966400000), y: 2798},
                	{x: new Date(1497052800000), y: 2791},
                	{x: new Date(1497139200000), y: 2791},
                	{x: new Date(1497225600000), y: 2791},
                	{x: new Date(1497312000000), y: 2806},
                	{x: new Date(1497398400000), y: 2903},
                	{x: new Date(1497484800000), y: 2901},
                	{x: new Date(1497571200000), y: 2904},
                	{x: new Date(1497657600000), y: 2957},
                	{x: new Date(1497744000000), y: 2957},
                	{x: new Date(1497830400000), y: 2953},
                	{x: new Date(1497916800000), y: 2978},
                	{x: new Date(1498003200000), y: 3017},
                	{x: new Date(1498089600000), y: 3149},
                	{x: new Date(1498176000000), y: 3313},
                	{x: new Date(1498262400000), y: 3354},
                	{x: new Date(1498348800000), y: 3354},
                	{x: new Date(1498435200000), y: 3344},
                	{x: new Date(1498521600000), y: 3347},
                	{x: new Date(1498608000000), y: 3346},
                	{x: new Date(1498694400000), y: 3342},
                	{x: new Date(1498780800000), y: 3639},
                	{x: new Date(1498867200000), y: 3859},
                	{x: new Date(1498953600000), y: 3858},
                	{x: new Date(1499040000000), y: 3855},
                	{x: new Date(1499126400000), y: 3863},
                	{x: new Date(1499212800000), y: 3863},
                	{x: new Date(1499299200000), y: 3870},
                	{x: new Date(1499385600000), y: 5612},
                	{x: new Date(1499472000000), y: 5656},
                	{x: new Date(1499558400000), y: 5656},
                	{x: new Date(1499644800000), y: 5656},
                	{x: new Date(1499731200000), y: 5657},
                	{x: new Date(1499817600000), y: 5658},
                	{x: new Date(1499904000000), y: 5660},
                	{x: new Date(1499990400000), y: 5649},
                	{x: new Date(1500076800000), y: 5658},
                	{x: new Date(1500163200000), y: 5657},
                	{x: new Date(1500249600000), y: 5657},
                	{x: new Date(1500336000000), y: 5658},
                	{x: new Date(1500422400000), y: 5656},
                	{x: new Date(1500508800000), y: 5654},
                	{x: new Date(1500595200000), y: 5653},
                	{x: new Date(1500681600000), y: 5657},
                	{x: new Date(1500768000000), y: 5657},
                	{x: new Date(1500854400000), y: 5657},
                	{x: new Date(1500940800000), y: 5655},
                	{x: new Date(1501027200000), y: 5655},
                	{x: new Date(1501113600000), y: 5656},
                	{x: new Date(1501200000000), y: 5653},
                	{x: new Date(1501286400000), y: 5658},
                	{x: new Date(1501372800000), y: 5658},
                	{x: new Date(1501459200000), y: 5657},
                	{x: new Date(1501545600000), y: 5655},
                	{x: new Date(1501632000000), y: 5657},
                	{x: new Date(1501718400000), y: 5660},
                	{x: new Date(1501804800000), y: 5659},
                	{x: new Date(1501891200000), y: 5658},
                	{x: new Date(1501977600000), y: 5658},
                	{x: new Date(1502064000000), y: 5658},
                	{x: new Date(1502150400000), y: 5668},
                	{x: new Date(1502236800000), y: 5668},
                	{x: new Date(1502323200000), y: 5669},
                	{x: new Date(1502409600000), y: 5750},
                	{x: new Date(1502496000000), y: 5754},
                	{x: new Date(1502582400000), y: 5754},
                	{x: new Date(1502668800000), y: 5754},
                	{x: new Date(1502755200000), y: 5752},
                	{x: new Date(1502841600000), y: 5752},
                	{x: new Date(1502928000000), y: 5751},
                	{x: new Date(1503014400000), y: 5748},
                	{x: new Date(1503100800000), y: 5744},
                	{x: new Date(1503187200000), y: 5744},
                	{x: new Date(1503273600000), y: 5744},
                	{x: new Date(1503360000000), y: 5739},
                	{x: new Date(1503446400000), y: 5737},
                	{x: new Date(1503532800000), y: 5731},
                	{x: new Date(1503619200000), y: 5724},
                	{x: new Date(1503705600000), y: 5721},
                	{x: new Date(1503792000000), y: 5721},
                	{x: new Date(1503878400000), y: 5721},
                	{x: new Date(1503964800000), y: 5712},
                	{x: new Date(1504051200000), y: 5709},
                	{x: new Date(1504137600000), y: 5708},
                	{x: new Date(1504224000000), y: 5708},
                	{x: new Date(1504310400000), y: 5708},
                	{x: new Date(1504396800000), y: 5708},
                	{x: new Date(1504483200000), y: 5708},
                	{x: new Date(1504569600000), y: 5708},
                	{x: new Date(1504656000000), y: 5707},
                	{x: new Date(1504742400000), y: 5707},
                	{x: new Date(1504828800000), y: 5705},
                	{x: new Date(1504915200000), y: 5705},
                	{x: new Date(1505001600000), y: 5705},
                	{x: new Date(1505088000000), y: 5705},
                	{x: new Date(1505174400000), y: 5705},
                	{x: new Date(1505260800000), y: 5705},
                	{x: new Date(1505347200000), y: 5704},
                	{x: new Date(1505433600000), y: 5704},
                	{x: new Date(1505520000000), y: 5703},
                	{x: new Date(1505606400000), y: 5703},
                	{x: new Date(1505692800000), y: 5703},
                	{x: new Date(1505779200000), y: 5703},
                	{x: new Date(1505865600000), y: 5702},
                	{x: new Date(1505952000000), y: 5701},
                	{x: new Date(1506038400000), y: 5700},
                	{x: new Date(1506124800000), y: 5699},
                	{x: new Date(1506211200000), y: 5699},
                	{x: new Date(1506297600000), y: 5699},
                	{x: new Date(1506384000000), y: 5699},
                	{x: new Date(1506470400000), y: 5698},
                	{x: new Date(1506556800000), y: 5697},
                	{x: new Date(1506643200000), y: 5697},
                	{x: new Date(1506729600000), y: 5694},
                	{x: new Date(1506816000000), y: 5694},
                	{x: new Date(1506902400000), y: 5694},
                	{x: new Date(1506988800000), y: 5694},
                	{x: new Date(1507075200000), y: 5691},
                	{x: new Date(1507161600000), y: 5691},
                	{x: new Date(1507248000000), y: 5691},
                	{x: new Date(1507334400000), y: 5688},
                	{x: new Date(1507420800000), y: 5688},
                	{x: new Date(1507507200000), y: 5688},
                	{x: new Date(1507593600000), y: 5688},
                	{x: new Date(1507680000000), y: 5687},
                	{x: new Date(1507766400000), y: 5687},
                	{x: new Date(1507852800000), y: 5686},
                	{x: new Date(1507939200000), y: 5686},
                	{x: new Date(1508025600000), y: 5686},
                	{x: new Date(1508112000000), y: 5686},
                	{x: new Date(1508198400000), y: 5686},
                	{x: new Date(1508284800000), y: 5685},
                	{x: new Date(1508371200000), y: 5685},
                	{x: new Date(1508457600000), y: 5682},
                	{x: new Date(1508544000000), y: 5682},
                	{x: new Date(1508630400000), y: 5681},
                	{x: new Date(1508716800000), y: 5681},
                	{x: new Date(1508803200000), y: 5681},
                	{x: new Date(1508889600000), y: 5679},
                	{x: new Date(1508976000000), y: 5679},
                	{x: new Date(1509062400000), y: 5679},
                	{x: new Date(1509148800000), y: 5679},
                	{x: new Date(1509235200000), y: 5679},
                	{x: new Date(1509321600000), y: 5679},
                	{x: new Date(1509408000000), y: 5671},
                	{x: new Date(1509494400000), y: 5671},
                	{x: new Date(1509580800000), y: 5672},
                	{x: new Date(1509667200000), y: 5673},
                	{x: new Date(1509753600000), y: 5673},
                	{x: new Date(1509840000000), y: 5673},
                	{x: new Date(1509926400000), y: 5673},
                	{x: new Date(1510012800000), y: 5674},
                	{x: new Date(1510099200000), y: 5672},
                	{x: new Date(1510185600000), y: 5672},
                	{x: new Date(1510272000000), y: 5671},
                	{x: new Date(1510358400000), y: 5670},
                	{x: new Date(1510444800000), y: 5670},
                	{x: new Date(1510531200000), y: 5670},
                	{x: new Date(1510617600000), y: 5670},
                	{x: new Date(1510704000000), y: 5670},
                	{x: new Date(1510790400000), y: 5670},
                	{x: new Date(1510876800000), y: 5670},
                	{x: new Date(1510963200000), y: 5669},
                	{x: new Date(1511049600000), y: 5669},
                	{x: new Date(1511136000000), y: 5669},
                	{x: new Date(1511222400000), y: 5667}
                  ]
              },
              {
                  name: 'Spring 2018',
                  data: [
                        {x: new Date(1509494400000), y: 5666},
                        {x: new Date(1509580800000), y: 5666},
                        {x: new Date(1509667200000), y: 5665},
                        {x: new Date(1509753600000), y: 5665},
                        {x: new Date(1509840000000), y: 5665},
                        {x: new Date(1509926400000), y: 5665},
                        {x: new Date(1510012800000), y: 5666},
                        {x: new Date(1510099200000), y: 5665},
                        {x: new Date(1510185600000), y: 5665},
                        {x: new Date(1510272000000), y: 5664},
                        {x: new Date(1510358400000), y: 5616},
                        {x: new Date(1510444800000), y: 5616},
                        {x: new Date(1510531200000), y: 5616},
                        {x: new Date(1510617600000), y: 5548},
                        {x: new Date(1510704000000), y: 5502},
                        {x: new Date(1510790400000), y: 5519},
                        {x: new Date(1510876800000), y: 5518},
                        {x: new Date(1510963200000), y: 5519},
                        {x: new Date(1511049600000), y: 5519},
                        {x: new Date(1511136000000), y: 5519},
                        {x: new Date(1511222400000), y: 5518}
                  ]
              }
            ]
        };

        var assignmentsGraphOptions = {
          lineSmooth: false,
          //low: 0,
          //high: 800,
          showArea: true,
          height: "245px",
          showLine: false,
          showPoint: false,
          axisX: {
            showGrid: false,
            type: Chartist.FixedScaleAxis,
            divisor: 14,
            labelInterpolationFnc: function (value, index, labels) {
                return moment(value).format('MMM D');
            }
          }
        };

        var assignmentsGraphResponsive = [
          ['screen and (max-width: 640px)', {
            axisX: {
              labelInterpolationFnc: function (value) {
                return moment(value).format('MMM D');
              }
            }
          }]
        ];

        Chartist.Line('#chartHours', dataAssignments, assignmentsGraphOptions, assignmentsGraphResponsive);


        var data = {
          labels: ['Spring 2017', 'Summer 1 2017', 'Summer 2 2017', 'Fall 2017', 'Spring 2018'],
          series: [
            [542, 443, 320, 780, 553],
            [412, 243, 280, 580, 453]
          ]
        };

        var options = {
            seriesBarDistance: 10,
            axisX: {
                showGrid: false
            },
            height: "245px"
        };

        var responsiveOptions = [
          ['screen and (max-width: 640px)', {
            seriesBarDistance: 5,
            axisX: {
              labelInterpolationFnc: function (value) {
                return value[0];
              }
            }
          }]
        ];

        Chartist.Bar('#chartActivity', data, options, responsiveOptions);

        var dataPreferences = {
            series: [
                [25, 30, 20, 25]
            ]
        };

        var optionsPreferences = {
            donut: true,
            donutWidth: 40,
            startAngle: 0,
            total: 100,
            showLabel: false,
            axisX: {
                showGrid: false
            }
        };

        Chartist.Pie('#chartPreferences', dataPreferences, optionsPreferences);

        Chartist.Pie('#chartPreferences', {
          labels: ['{CLASS_BREAK_FR}%','{CLASS_BREAK_SO}%','{CLASS_BREAK_JR}%', '{CLASS_BREAK_SR}%'],
          series: [{CLASS_BREAK_FR}, {CLASS_BREAK_SO}, {CLASS_BREAK_JR}, {CLASS_BREAK_SR}]
        });
    });
</script>

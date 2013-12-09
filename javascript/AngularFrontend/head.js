<script src="{JAVASCRIPT_BASE}/AngularFrontend/angular.min.js"></script>

<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/20bdd8fe.plugins.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/34dfa171.modules.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/5149178c.scripts.js"></script>

<script type="text/javascript">

	(function() {
		angular.module('roomDamages')
			.config(function(roomDamageBrokerProvider){
				roomDamageBrokerProvider.setDamageTypes({DAMAGE_TYPES});
				roomDamageBrokerProvider.setLocation('index.php');
			});
	})();
	
	(function() {
		angular.module('roomDamages')
			.config(function(roomDamageResidentProvider){
				roomDamageResidentProvider.setAssignment({ASSIGNMENT});
				roomDamageResidentProvider.setResidents({RESIDENTS});
				roomDamageResidentProvider.setStudent({STUDENT});
				roomDamageResidentProvider.setCheckin({CHECKIN});
			});
	})();
</script>
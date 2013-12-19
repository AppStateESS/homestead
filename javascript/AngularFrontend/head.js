<script src="{JAVASCRIPT_BASE}/AngularFrontend/angular.min.js"></script>

<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/20bdd8fe.plugins.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/34dfa171.modules.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/1c7be9a3.scripts.js"></script>

<script type="text/javascript">

	(function() {
		angular.module('roomDamages')
			.config(function(roomDamageBrokerProvider){
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
		
		angular.module('roomDamages')
		.config(function(roomDamageTypeProvider){
			roomDamageTypeProvider.setDamageTypes({DAMAGE_TYPES});
		});
		
		angular.module('roomDamages')
		.config(function(roomDamageAssessmentProvider){
			roomDamageAssessmentProvider.setTerm({TERM});
			roomDamageAssessmentProvider.setLocation('index.php');
		});
	})();
</script>
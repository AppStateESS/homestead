<script src="{JAVASCRIPT_BASE}/AngularFrontend/angular.min.js"></script>

<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/7e110f8b.plugins.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/34dfa171.modules.js"></script>
<script src="{JAVASCRIPT_BASE}/AngularFrontend/scripts/b518f2a0.scripts.js"></script>

<script type="text/javascript">

	(function() {
		angular.module('roomDamages')
			.config(function(roomDamageBrokerProvider){
				roomDamageBrokerProvider.setDamageTypes({DAMAGE_TYPES})
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
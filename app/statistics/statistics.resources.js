(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('StatisticsResource', StatisticsResource);

	function StatisticsResource(resourceExtension) {
		return resourceExtension('statistics/:id');
	}

})();
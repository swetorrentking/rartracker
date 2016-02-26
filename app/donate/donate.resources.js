(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('DonationsResource', DonationsResource);

	function DonationsResource(resourceExtension) {
		return resourceExtension('donations/:id', { id: '@id' });
	}

})();
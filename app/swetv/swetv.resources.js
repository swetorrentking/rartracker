(function(){
	'use strict';

	angular
		.module('app.swetv')
		.factory('SweTvResource', SweTvResource);

	function SweTvResource(resourceExtension) {
		return {
			Channels:		resourceExtension('swetv/channels/:id', { id: '@id' }),
			Programs:		resourceExtension('swetv/programs/:id', { id: '@id' }),
			Guess:			resourceExtension('swetv/guess'),
		};
	}

})();

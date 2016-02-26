(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('BonusShopResource', BonusShopResource);

	function BonusShopResource(resourceExtension) {
		return resourceExtension('bonus-shop/:id', { id: '@id' });
	}

})();
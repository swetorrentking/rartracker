(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('FaqResource', FaqResource)
		.factory('RulesResource', RulesResource);

	function FaqResource(resourceExtension) {
		return resourceExtension('faq/:id', { id: '@id' });
	}

	function RulesResource(resourceExtension) {
		return resourceExtension('rules/:id', { id: '@id' });
	}

})();
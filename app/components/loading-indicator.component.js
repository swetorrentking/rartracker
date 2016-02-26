(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('loadingIndicator', {
			bindings: {
				hide: '='
			},
			template: '<div ng-hide="$ctrl.hide" class="fa-spinner fa-spin fa-4x fa-fw spinner"></div>'
		});

})();

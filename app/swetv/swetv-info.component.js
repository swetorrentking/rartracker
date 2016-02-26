(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('swetvInfo', {
			bindings: {
				torrent: '<',
				tvChannel: '<'
			},
			templateUrl: '../app/swetv/swetv-info.component.template.html',
			controllerAs: 'vm'
		});

})();

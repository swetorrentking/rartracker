(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('userPeers', {
			bindings: {
				peers: '<',
			},
			templateUrl: '../app/user/user-peers.component.template.html'
		});

})();

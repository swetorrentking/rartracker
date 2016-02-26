(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('LeechbonusController', LeechbonusController);

	function LeechbonusController(user) {

		this.currentUser = user;

	}

})();

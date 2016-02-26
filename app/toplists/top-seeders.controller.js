(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('TopSeedersController', TopSeedersController);

	function TopSeedersController(UsersResource) {

		UsersResource.Users.get({id: 'topseeders'}, (data) => {
			this.newSeeders = data.new;
			this.archiveSeeders = data.archive;
		}, () => {
			this.error = true;
		});

	}

})();
(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('TopLeechbonusController', TopLeechbonusController);

	function TopLeechbonusController(UsersResource) {

		UsersResource.Users.query({id: 'leechbonustop'}, (data) => {
			this.users = data;
		}, () => {
			this.error = true;
		});

	}

})();
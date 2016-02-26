(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(FriendsRoutes);

	function FriendsRoutes($stateProvider) {

		$stateProvider
			.state('friends', {
				parent		: 'header',
				url			: '/friends',
				views		: {
					'content@': {
						templateUrl : '../app/friends/friends.template.html',
						controller  : 'FriendsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				}
			});

	}

}());
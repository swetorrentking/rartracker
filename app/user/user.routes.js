(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(UserRoutes);

	function UserRoutes($stateProvider) {

		$stateProvider
			.state('user', {
				parent		: 'header',
				url			: '/user/:id/:username',
				views		: {
					'content@': {
						templateUrl : '../app/user/user.template.html',
						controller  : 'UserController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				}
			})
			.state('edit-user', {
				parent		: 'header',
				url			: '/user/:id/:username/edit',
				views		: {
					'content@': {
						templateUrl : '../app/user/user-edit.template.html',
						controller  : 'UserEditController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				}
			})
			.state('user-comments', {
				parent		: 'header',
				url			: '/user/:id/:username/comments?page',
				views		: {
					'content@': {
						templateUrl : '../app/user/comments.template.html',
						controller  : 'UserTorrentComments as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params		: {
					page: {
						value: '1',
						squash: true
					}
				}
			});

	}

}());

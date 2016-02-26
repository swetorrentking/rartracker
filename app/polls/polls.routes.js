(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(PollsRoutes);

	function PollsRoutes($stateProvider) {

		$stateProvider
			.state('polls', {
				parent		: 'header',
				url			: '/polls?page',
				views		: {
					'content@': {
						templateUrl : '../app/polls/polls.template.html',
						controller  : 'PollsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params: {
					page: {
						value: '1',
						squash: true
					}
				}
			});

	}

}());

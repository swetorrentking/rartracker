(function(){
	'use strict';

	angular
		.module('app.requests')
		.config(RequestRoutes);

	function RequestRoutes($stateProvider) {

		$stateProvider
			.state('requests', {
				parent		: 'header',
				url			: '/requests',
				views		: {
					'content@': {
						templateUrl : '../app/requests/requests-nav.template.html',
					}
				},
				redirectTo	: 'requests.requests'
			})
			.state('requests.requests', {
				url			: '?page&sort&order',
				templateUrl : '../app/requests/requests.template.html',
				controller  : 'RequestsController as vm',
				resolve		: { user: authService => authService.getPromise() },
				params: {
					page: {
						value: '1',
						squash: true
					},
					sort: {
						value: 'added',
						squash: true
					},
					order: {
						value: 'desc',
						squash: true
					}
				}
			})
			.state('requests.request', {
				url			: '/:id/:slug',
				templateUrl : '../app/requests/request.template.html',
				controller  : 'RequestController as vm',
				params		: { scrollTo: ''},
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('requests.add', {
				url			: '/add',
				templateUrl : '../app/requests/add-request.template.html',
				controller  : 'AddRequestController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('requests.edit', {
				url			: '/:id/:slug/edit',
				templateUrl : '../app/requests/edit-request.template.html',
				controller  : 'EditRequestController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('requests.my', {
				url			: '/my',
				templateUrl : '../app/requests/my-requests.template.html',
				controller  : 'MyRequestsController as vm',
				resolve		: { user: authService => authService.getPromise() }
			});

	}

}());

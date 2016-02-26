(function(){
	'use strict';

	angular
		.module('app.suggestions')
		.config(SuggestionsRoutes);

	function SuggestionsRoutes($stateProvider) {

		$stateProvider
			.state('suggestions', {
				parent		: 'header',
				url			: '/suggestions?view',
				views			: {
					'content@': {
						templateUrl : '../app/suggestions/suggestions.template.html',
						controller  : 'SuggestionsController as vm',
						resolve		: { user: authService => authService.getPromise() },
					}
				},
				params		: {
					view: {
						value: 'hot',
						squash: true
					}
				}
			});

	}

}());

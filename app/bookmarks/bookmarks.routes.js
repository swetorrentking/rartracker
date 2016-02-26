(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(BookmarksRoutes);

	function BookmarksRoutes($stateProvider) {

		$stateProvider
			.state('bookmarks', {
				parent		: 'header',
				url			: '/bookmarks',
				views		: {
					'content@': {
						templateUrl : '../app/bookmarks/bookmarks.template.html',
						controller  : 'BookmarksController as vm',
					}
				}
			});

	}

}());
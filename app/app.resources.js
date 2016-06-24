(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('ReportsResource', ReportsResource)
		.factory('MovieDataResource', MovieDataResource);

	function ReportsResource(resourceExtension) {
		return resourceExtension('reports/:id', { id: '@id' });
	}

	function MovieDataResource(resourceExtension) {
		return {
			Data:				resourceExtension('moviedata/:id', { id: '@id' }),
			Imdb:				resourceExtension('moviedata/imdb/:id', { id: '@id' }),
			Search:				resourceExtension('moviedata/search'),
			Guess:				resourceExtension('moviedata/guess'),
			Youtube:			resourceExtension('moviedata/:id/youtube', { id: '@id' }),
			Refresh:			resourceExtension('moviedata/:id/refresh'),
		};
	}

})();

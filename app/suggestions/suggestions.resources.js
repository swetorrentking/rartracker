(function(){
	'use strict';

	angular
		.module('app.suggestions')
		.factory('SuggestionsResource', SuggestionsResource);

	function SuggestionsResource(resourceExtension) {
		return {
			Votes:		resourceExtension('suggestions/:id/votes/:voteId', { id: '@id', voteId: '@voteId'  }),
			Suggest:	resourceExtension('suggestions/:id', { id: '@id' }),
		};
	}

})();
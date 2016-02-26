(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('PollsResource', PollsResource);

	function PollsResource(resourceExtension) {
		return {
			Polls:		resourceExtension('polls/:id', { id: '@id' }),
			Latest:		resourceExtension('polls/latest'),
			Votes:		resourceExtension('polls/votes/:id', { id: '@id' }),
		};
	}

})();
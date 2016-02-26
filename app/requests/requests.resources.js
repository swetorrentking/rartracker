(function(){
	'use strict';

	angular
		.module('app.requests')
		.factory('RequestsResource', RequestsResource);

	function RequestsResource(resourceExtension) {
		return {
			Votes:				resourceExtension('requests/:id/votes/:voteId', { id: '@id', voteId: '@voteId'  }),
			Requests:			resourceExtension('requests/:id', { id: '@id' }),
			My:					resourceExtension('requests/my'),
			Comments:			resourceExtension('requests/:requestId/comments/:id' , { id: '@id', requestId: '@requestId'  }),
		};
	}

})();
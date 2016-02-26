(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(UploadRoutes);

	function UploadRoutes($stateProvider) {

		$stateProvider
			.state('upload', {
				parent		: 'header',
				url			: '/upload',
				views		: {
					'content@': {
						templateUrl : '../app/upload/upload.template.html',
						controller  : 'UploadController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params		: { requestId: null, requestName: null },
			});

	}

}());
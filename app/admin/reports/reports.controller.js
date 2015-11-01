(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('ReportsController', function ($scope, SendMessageDialog, user, ErrorDialog, DeleteDialog, AuthService, AdminResource, SubtitlesResource, RequestsResource, TorrentsResource, ForumResource) {
			$scope.itemsPerPage = 10;

			var loadReports = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				AdminResource.Reports.query({
					'limit': $scope.itemsPerPage,
					'index': index
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.reports = data;
				});
			};

			$scope.handle = function (report) {
				AdminResource.Reports.update({
					id: report.id
				}, function () {
					AuthService.readNewReports();
					report.handledBy = user;
				}, function (error) {
					ErrorDialog.display(error.data);
				});
			};

			$scope.delete = function (report) {
				AdminResource.Reports.delete({
					id: report.id
				}, function () {
					report.removed = true;
				}, function (error) {
					ErrorDialog.display(error.data);
				});
			};

			$scope.deletePost = function (report){
				ForumResource.Posts.delete({
					forumid: report.post.forumid,
					topicid: report.post.topicid,
					id: report.post.id
				}, function () {
					$scope.delete(report);
				}, function (error) {
					ErrorDialog.display(error.data);
				});
			};

			$scope.deleteComment = function (report){
				TorrentsResource.Comments.delete({
					id: report.comment.torrent,
					commentId: report.comment.id,
				}, function () {
					$scope.delete(report);
				}, function (error) {
					ErrorDialog.display(error.data);
				});
			};

			$scope.deleteSubtitle = function (report){
				var dialog = DeleteDialog('Radera undertext', 'Vill du radera undertexten?', true, report.reason);

				dialog.then(function (reason) {
					SubtitlesResource.delete({
							id: report.subtitle.id,
							reason: reason
						}, function () {
							$scope.delete(report);
						}, function (error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			$scope.deleteRequest = function (report) {
				var dialog = DeleteDialog('Radera request', 'Vill du radera requesten \''+report.request.request+'\'?', true, report.reason);

				dialog.then(function (reason) {
					RequestsResource.Requests.delete({
							id: report.request.id,
							reason: reason
						}, function () {
							$scope.delete(report);
						}, function (error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			$scope.deleteTorrent = function (report) {
				TorrentsResource.Torrents.remove({
						id: report.torrent.id,
						reason: report.reason,
						pmUploader: report.pmUploader,
						pmPeers: report.pmPeers,
						banRelease: report.banRelease,
						attachTorrentId: report.attachTorrentId,
						restoreRequest: report.restoreRequest,
					}, function () {
						$scope.delete(report);
					}, function (error) {
						ErrorDialog.display(error.data);
					});
			};

			$scope.sendMessage = function (user) {
				new SendMessageDialog({user: user});
			};

			$scope.pageChanged = function () {
				loadReports();
			};

			loadReports();

		});
})();
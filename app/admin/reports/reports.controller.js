(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('ReportsController', ReportsController);

	function ReportsController($state, $stateParams, SendMessageDialog, user, ErrorDialog, DeleteDialog, authService, AdminResource, SubtitlesResource, RequestsResource, TorrentsResource, ForumResource) {

		this.currentUser = user;
		this.itemsPerPage = 10;
		this.currentPage = $stateParams.page;

		this.loadReports = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			AdminResource.Reports.query({
				'limit': this.itemsPerPage,
				'index': index
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.reports = data;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.handle = function (report) {
			AdminResource.Reports.update({
				id: report.id
			}, () => {
				authService.readNewReports();
				report.handledBy = user;
			}, (error) => {
				ErrorDialog.display(error.data);
			});
		};

		this.delete = function (report) {
			AdminResource.Reports.delete({
				id: report.id
			}, () => {
				report.removed = true;
			}, (error) => {
				ErrorDialog.display(error.data);
			});
		};

		this.deletePost = function (report){
			ForumResource.Posts.delete({
				forumid: report.post.forumid,
				topicid: report.post.topicid,
				id: report.post.id
			}, () => {
				this.delete(report);
			}, (error) => {
				ErrorDialog.display(error.data);
			});
		};

		this.deleteComment = function (report){
			TorrentsResource.Comments.delete({
				id: report.comment.torrent,
				commentId: report.comment.id,
			}, () => {
				this.delete(report);
			}, (error) => {
				ErrorDialog.display(error.data);
			});
		};

		this.deleteSubtitle = function (report){
			DeleteDialog('Radera undertext', 'Vill du radera undertexten?', true, report.reason)
				.then((reason) => {
					return SubtitlesResource.delete({
						id: report.subtitle.id,
						reason: reason
					});
				})
				.then(() => {
					this.delete(report);
				})
				.catch((error) => {
					if (error) {
						ErrorDialog.display(error.data);
					}
				});
		};

		this.deleteRequest = function (report) {
			DeleteDialog('Radera request', 'Vill du radera requesten \''+report.request.request+'\'?', true, report.reason)
				.then((reason) => {
					return RequestsResource.Requests.delete({
						id: report.request.id,
						reason: reason
					});
				})
				.then(() => {
					this.delete(report);
				})
				.catch((error) => {
					if (error) {
						ErrorDialog.display(error.data);
					}
				});
		};

		this.deleteTorrent = function (report) {
			TorrentsResource.Torrents.remove({
					id: report.torrent.id,
					reason: report.reason,
					pmUploader: report.pmUploader,
					pmPeers: report.pmPeers,
					banRelease: report.banRelease,
					attachTorrentId: report.attachTorrentId,
					restoreRequest: report.restoreRequest,
				}, () => {
					this.delete(report);
				}, (error) => {
					ErrorDialog.display(error.data);
				});
		};

		this.sendMessage = function (user) {
			new SendMessageDialog({user: user});
		};

		this.loadReports();

	}

})();

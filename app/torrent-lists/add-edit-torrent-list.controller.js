(function(){
	'use strict';

	angular
		.module('app.torrentLists')
		.controller('AddEditTorrentListController', AddEditTorrentListController);

	function AddEditTorrentListController($state, $stateParams, TorrentsResource, MovieDataResource, TorrentListsResource, user, $uibModal) {

		this.currentUser = user;

		if ($stateParams.id) {
			if ($stateParams.torrentList) {
				this.listModel = $stateParams.torrentList;
			} else {
				TorrentListsResource.Lists.get({ id: $stateParams.id }).$promise
					.then((torrentList) => {
						this.listModel = torrentList;
					});
			}
		} else {
			this.listModel = {
				imdbUrl: '',
				imdbid: 0,
				comment: '',
				type: 'unlisted',
				torrents: [],
				torrents_data: []
			};
		}

		this.fetchRelatedTorrents = function (id) {
			TorrentsResource.Related.query({id: id}, (torrents) => {
				this.relatedTorrents = torrents;
			});
		};

		this.createList = function () {
			this.closeAlert();
			TorrentListsResource.Lists.save({}, this.listModel).$promise
				.then((response) => {
					$state.go('torrent-lists.torrent-list', {id: response.id, slug: response.slug});
				})
				.catch((error) => {
					this.addAlert({ type: 'danger', msg: error.data });
				});
		};

		this.updateList = function () {
			TorrentListsResource.Lists.update(this.listModel).$promise
				.then(() => {
					$state.go('torrent-lists.torrent-list', {id: this.listModel.id, slug: this.listModel.slug});
				})
				.catch((error) => {
					this.addAlert({ type: 'danger', msg: error.data });
				});
		};

		this.changedCategory = function () {
			if (this.imdbLessCategories.indexOf(this.listModel.category) > -1) {
				 this.noimdb = true;
			} else {
				this.noimdb = false;
			}
		};

		this.fetchImdbInfo = function () {
			if (this.listModel.imdbUrl.length > 1) {
				var match = this.listModel.imdbUrl.match(/\/(tt[0-9]+)(\/|$)/);
				if (match && match.length > 1){
					var imdbid = match[1];
					MovieDataResource.Imdb.get({id: imdbid}, (imdb) => {
						this.listModel.imdbInfo = imdb['title'] + ' (' + imdb['year'] +')';
						this.listModel.imdbid = imdb['id'];
					}, (error) => {
						this.listModel.imdbInfo = 'Error: ' + error;
					});
				}
			}
		};

		this.removeTorrent = function (torrent) {
			var index;
			// Remove from id-list
			index = this.listModel.torrents.indexOf(torrent.id);
			this.listModel.torrents.splice(index, 1);

			// Remove from torrent-list
			index = this.listModel.torrents_data.indexOf(torrent);
			this.listModel.torrents_data.splice(index, 1);
		};

		this.addTorrents = function () {
			$uibModal.open({
				animation: true,
				templateUrl: '../app/dialogs/pick-torrents-dialog.template.html',
				controller: 'PickTorrentsDialogController as vm',
				backdrop: 'static',
				size: 'lg',
				resolve: {}
			})
			.result
			.then((torrents) => {

				torrents.forEach((torrent) => {
					if (!this.listModel.torrents_data.filter(t => torrent.id === t.id)[0]) {
						this.listModel.torrents_data.push(torrent);
					}
				});
				this.listModel.torrents = this.listModel.torrents_data.map(torrent => torrent.id);
				this.listModel.torrents_data.sort((a, b) => {
					if (a.name < b.name) return -1;
					if (a.name > b.name) return 1;
					return 0;
				});
			});
		};

		this.removeImdb = function () {
			this.listModel.imdbid = 0;
			this.listModel.imdbUrl = '';
			this.listModel.imdbInfo = '';
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function () {
			this.alert = null;
		};

	}

})();

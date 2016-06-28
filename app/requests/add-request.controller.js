(function(){
	'use strict';

	angular
		.module('app.requests')
		.controller('AddRequestController', AddRequestController);

	function AddRequestController($state, $stateParams, categories, TorrentsResource, MovieDataResource, RequestsResource, user) {

		this.currentUser = user;
		this.categories = categories;
		this.noimdb = false;
		this.imdbLessCategories = [
			categories.TV_SWE.id,
			categories.AUDIOBOOKS.id,
			categories.EBOOKS.id,
			categories.EPAPERS.id,
			categories.MUSIC.id,
		];

		if ($stateParams.id) {
			RequestsResource.Requests.get({ id: $stateParams.id }).$promise
				.then((data) => {
					this.requestParams = {
						id: data.request.id,
						category: data.request.type,
						comment: data.request.comment,
						season: data.request.season,
						imdbId: data.request.imdbid,
						customName: data.request.request,
						imdbInfo: data.request.request,
						seasons: []
					};
					for (var i = 0; i < data.movieData['seasoncount']; i++) {
						this.requestParams.seasons.push(i+1);
					}
					this.loaded = true;
					this.changedCategory();
				})
				.catch((error) => {
					this.notFoundMessage = error.data;
				});
		} else {
			this.requestParams = {
				category: 1,
				imdbUrl: '',
				imdbId: 0,
				season: 0,
				seasons: [],
				comment: '',
			};
		}

		this.fetchRelatedTorrents = function (id) {
			TorrentsResource.Related.query({id: id}, (torrents) => {
				this.relatedTorrents = torrents;
			});
		};

		this.addRequest = function () {
			this.closeAlert();
			RequestsResource.Requests.save({}, this.requestParams).$promise
				.then((response) => {
					$state.go('requests.request', {id: response.id, slug: response.slug});
				})
				.catch((error) => {
					this.addAlert({ type: 'danger', msg: error.data });
				});
		};

		this.updateRequest = function () {
			this.closeAlert();
			RequestsResource.Requests.update({id: $stateParams.id}, this.requestParams).$promise
				.then((response) => {
					$state.go('requests.request', {id: response.id, name: response.name});
				})
				.catch((error) => {
					this.addAlert({ type: 'danger', msg: error.data });
				});
		};

		this.changedCategory = function () {
			if (this.imdbLessCategories.indexOf(this.requestParams.category) > -1) {
				 this.noimdb = true;
			} else {
				this.noimdb = false;
			}
		};

		this.fetchImdbInfo = function () {
			this.closeAlert();
			if (this.requestParams.imdbUrl.length > 1) {
				var match = this.requestParams.imdbUrl.match(/\/(tt[0-9]+)(\/|$)/);
				if (match && match.length > 1){
					var imdbId = match[1];
					MovieDataResource.Imdb.get({id: imdbId}, (imdb) => {
						this.requestParams.imdbInfo = imdb['title'] + ' (' + imdb['year'] +')';
						this.requestParams.imdbId = imdb['id'];
						for (var i = 0; i < imdb['seasoncount']; i++) {
							this.requestParams.seasons.push(i+1);
						}
						if (imdb['seasoncount'] > 0) {
							this.requestParams.season = this.requestParams.seasons[0];
						}
						this.fetchRelatedTorrents(imdb['id']);
					}, (error) => {
							this.requestParams.imdbInfo = 'Error: ' + error;
					});
				}
			}
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function () {
			this.alert = null;
		};

	}

})();

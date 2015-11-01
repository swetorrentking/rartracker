(function(){
	'use strict';

	angular
		.module('tracker.services')
		.service('DateService', function () {
			this.leadingZeros = function (date) {
				return date < 10 ? '0' + date : date;
			};

			this.getYMD = function (timestamp) {
				var date = new Date(timestamp*1000);
				return date.getFullYear() + '-' + this.leadingZeros((date.getMonth() + 1)) + '-' + this.leadingZeros(date.getDate());
			};

			this.getHI = function (timestamp) {
				var date = new Date(timestamp*1000);
				return this.leadingZeros(date.getHours()) + ':' + this.leadingZeros(date.getMinutes());
			};

			this.getWeekDay = function (timestamp) {
				var date = new Date(timestamp*1000);
				switch(date.getDay()) {
					case 0: return 'Söndag';
					case 1: return 'Måndag';
					case 2: return 'Tisdag';
					case 3: return 'Onsdag';
					case 4: return 'Torsdag';
					case 5: return 'Fredag';
					case 6: return 'Lördag';
					default: return '-';
				}
			};
		});
})();
'use strict';

/* Controllers */
var toddAtHomeApp = angular.module('toddAtHomeApp', ['ngRoute']);

toddAtHomeApp.controller('MenuController', function($scope, $location) {
	$scope.menuItems = [
	{"header" : "Cloud Services", "routeArg" : "cloud"},
	{"header" : "Zone Management", "routeArg" : "zone"}
	];
	
	$scope.navigateToSubTab = function(routeArg) {
		$location.path("/" + routeArg);
	};
});

toddAtHomeApp.controller('ZoneController', function($scope) {
});
toddAtHomeApp.controller('CloudController', function($scope) {
});

toddAtHomeApp.config(['$routeProvider', 
	function($routeProvider) {
		$routeProvider.
			when('/zone', {
				templateUrl: 'partials/zone.html',
				controller: 'ZoneController'
				}).
				when('/cloud', {
					templateUrl: 'partials/cloud.html',
					controller: 'CloudController'
				}).
				otherwise({
					redirectTo: '/zone'
					});
	}]);

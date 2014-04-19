'use strict';

/* Controllers */
var toddAtHomeApp = angular.module('toddAtHomeApp', ['ngRoute']);

toddAtHomeApp.controller('MenuController', function($scope, $location) {
	$scope.menuItems = [
	{"header" : "Cloud Services", "routeArg" : "cloud"},
	{"header" : "Zone Management", "routeArg" : "zone"},
	{"header" : "SSH Management", "routeArg" : "ssh"},
	{"header" : "GIT Repositories", "routeArg" : "git"}
	];
	
	$scope.navigateToSubTab = function(routeArg) {
		$location.path("/" + routeArg);
	};
});

toddAtHomeApp.controller('ZoneController', function($scope) {
});
toddAtHomeApp.controller('CloudController', function($scope, $http) {
    $scope.fileTypeToImage = function(fileType) {
	switch(fileType)
	{
	case 'folder':
	    return "/img/folder.png";
	case 'image':
	    return "/img/image.jpg";
	case 'text':
	    return "/img/text.jpg";
	case 'html':
	    return "/img/html.jpg";
	default:
	    return "/img/text.jpg";
	}
    };
    $scope.downloadFile = function(guid){
	$http.get('partials/cloud/download.php?guid='+ guid).success(function(data){
	    var fileView = document.getElementById("fileView");
	    fileView.style.display= "block";
	    fileView.innerHTML = data;
	});
    };
    $scope.deleteFile = function(guid){
	$http.get('partials/cloud/delete.php?guid='+guid).success(function(data){
	    $http.get('partials/cloud/listFiles.php').success(function(data){
		$scope.files = data;
	    });
	});
    };
    $http.get('partials/cloud/listFiles.php').success(function(data){
	$scope.files = data;
    });

    
});
toddAtHomeApp.controller('SshController', function($scope) {
});
toddAtHomeApp.controller('GitController', function($scope) {
});


toddAtHomeApp.config(['$routeProvider', 
	function($routeProvider) {
		$routeProvider.
			when('/zone', {
				templateUrl: 'partials/zone/zone.html',
				controller: 'ZoneController'
				}).
			when('/cloud', {
				templateUrl: 'partials/cloud/cloud.html',
				controller: 'CloudController'
				}).
			when('/ssh', {
				templateUrl: 'partials/ssh/ssh.html',
				controller: 'SshController'
				}).
			when('/git', {
				templateUrl: 'partials/git/git.html',
				controller: 'GitController'
			}).
			otherwise({
				redirectTo: '/cloud'
			});
	}]);

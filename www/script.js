
angular.module('BeerSquirrel', ['ngRoute', 'ngResource'])
	.config(function($routeProvider, $locationProvider){
		$routeProvider
			.when('/', {
				action: 'home',
				controller: 'Home',
				templateUrl: 'home.html'
			})
			.when('/view/:id', {
				action: 'view',
				controller: 'View',
				templateUrl: 'view.html'
			})
			.otherwise({
				redirectTo: '/'
			});

		$locationProvider.html5Mode(true);
	})

	.run(function($rootScope, $location) {
		$rootScope.isMobile = /ipad|iphone|ipod|android/i.test(navigator.userAgent.toLowerCase());
		
		if (/Macintosh/i.test(navigator.userAgent.toLowerCase())) {
			$rootScope.OS = 'mac';
		} else if (/Windows/i.test(navigator.userAgent.toLowerCase())) {
			$rootScope.OS = 'win';
		} else {
			$rootScope.OS = null;
		}

		
		$rootScope.$on('uploaded', function(e, f) {
			$rootScope.$apply(function($scope) {
				$scope.error = null;
			});
			$location.path('/view/' + f.uid);			
		});
		
		$rootScope.$on('large-file', function() {
			$rootScope.$apply(function($scope) {
				$scope.error = 'large-file';
			});
		});
		
		$rootScope.$on('upload-error', function() {
			$rootScope.$apply(function($scope) {
				$scope.error = 'large-file';
			});
		});
	})
	
	.service('UploadService', function($resource, $routeParams, $location, $rootScope) {

		var up = $resource('/upload', {}, {
			'upload': { 'method': 'POST', params : { 'action' : 'upload' }}
		});

		var file = $resource('/get/:id', {id: '@id'});

		this.upload = function(d) {
			var max = 2097152;

			if (d.data.length > max) {
				$rootScope.$broadcast('large-file');
			} else {
				up.upload({}, d, function(f) {
					$rootScope.$broadcast('uploaded', f);
				}, function() {
					$rootScope.$broadcast('upload-error');
				});
			}
		}

		this.get = function(id, callback) {
			var f = file.get({id: id}, function() {
				if (f.uid) {
					callback(f);
				} else {
					$rootScope.$broadcast('file-error');
				}
			}, function() {
				$rootScope.$broadcast('file-error');
			});
		}
		
		this.uploadFile = function(file) {

			var paste = {};
			var self = this;

			var fileReader = new FileReader();
			fileReader.onloadend = function(e) {
				paste.type = 'image';
				paste.data = this.result;
				self.upload(paste);
			};

			fileReader.onerror = function() {
				$rootScope.$broadcast('upload-error');
			};
			
			fileReader.readAsDataURL(file);			
		}
	})

	.controller('Home', function ($scope) {
		
	})

	.controller( 'View', function ($scope, $http, $routeParams, UploadService) {
		UploadService.get($routeParams.id, function(file) {
			$scope.file = file;
		});
	})
	
	.directive('uploadField', function($http, $location, UploadService) {
		return {
			restrict: 'A',
			link: function(scope, elem, attr, ctrl) {
				elem.bind('change', function(e) {
					UploadService.uploadFile(e.target.files[0]);
				});
			}
		};
	})

	.directive('uploadPaste', function($http, $location, UploadService) {
		return {
			restrict: 'A',
			link: function(scope, elem, attr, ctrl) {
				elem.bind('paste', function(e) {

					var paste = {};
		
					if (/text\/plain|text\/html/.test(e.clipboardData.types)) {
						paste.data = e.clipboardData.getData('text/plain');
						paste.type = 'txt';
						
						UploadService.upload(paste);
		
					} else if (/Files/.test(e.clipboardData.types)) {
		
						for (var i = 0; i < e.clipboardData.items.length; i++) {
							if (e.clipboardData.items[i].kind == 'file' && e.clipboardData.items[i].type == 'image/png') {
							
								e.preventDefault();
		
								var imageFile = e.clipboardData.items[i].getAsFile();
								UploadService.uploadFile(imageFile);
			
								// only paste 1 image at a time
								break;
							}
						}
					}
				});
			}
		};
	});



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

		$rootScope.back = function() {
			$location.path('/');	
		};
		
		$rootScope.safeApply = function(fn) {
			var phase = this.$root.$$phase;
			if (phase == '$apply' || phase == '$digest') {
				if (fn && (typeof(fn) === 'function')) {
					fn($rootScope);
				}
			} else {
				this.$apply(fn);
			}
		};
		
		if (/Macintosh/i.test(navigator.userAgent.toLowerCase())) {
			$rootScope.OS = 'mac';
		} else if (/Windows/i.test(navigator.userAgent.toLowerCase())) {
			$rootScope.OS = 'win';
		} else {
			$rootScope.OS = null;
		}

		$rootScope.$on('uploaded', function(e, f) {
			$rootScope.error = null;
			$location.path('/view/' + f.uid);	
		});
		
		$rootScope.$on('large-file', function() {
			$rootScope.safeApply(function($scope) {
				$scope.error = 'large-file';
			});
		});
		
		$rootScope.$on('upload-error', function() {
			$rootScope.safeApply(function($scope) {
				$scope.error = 'upload-error';
			});
		});
		
		$rootScope.$on('upload-start', function() {
			$location.path('/');
		});
	})
	
	.service('UploadService', function($resource, $routeParams, $location, $rootScope) {

		var up = $resource('/upload', {}, {
			'upload': { 'method': 'POST'}
		});

		var file = $resource('/get/:id', {id: '@id'});

		this.upload = function(d) {
			var max = 2097152;

			if (d.data.length > max) {
				$rootScope.$broadcast('large-file');
			} else {
				up.upload({}, d, function(f) {
					if (f.uid) {
						$rootScope.$broadcast('uploaded', f);
					} else {
						$rootScope.$broadcast('upload-error');
					}
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

			if (file.type) {
				var type = file.type.split('/');
	
				if (type[0] != 'text' && type[0] != 'image') {
					$rootScope.$broadcast('upload-error');
					return;
				}
			}
			
			var paste = {};
			var self = this;

			var fileReader = new FileReader();
			fileReader.onloadend = function(e) {
				paste.type = file.type;
				paste.data = this.result;
				self.upload(paste);
			};

			fileReader.onerror = function() {
				$rootScope.$broadcast('upload-error');
			};
			
			fileReader.readAsDataURL(file);			
		}
	})

	.controller('Home', function ($rootScope) {
		$rootScope.showBack = false;
	})

	.controller('View', function ($rootScope, $scope, $http, $routeParams, UploadService, $location) {
		$rootScope.showBack = true;

		UploadService.get($routeParams.id, function(file) {
			$scope.file = file;
			$scope.url = $location.absUrl();

			setTimeout(function() {
				var el = document.getElementById('upload-link');
				el.setSelectionRange(0, el.value.length)
				el.focus();
			});
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
			
				elem.bind('dragover', function(e) {
					e.preventDefault();
				});
			
				elem.bind('drop', function(e) {
					e.preventDefault();
					e.stopPropagation();
					
					var files = e.target.files || e.dataTransfer.files;
					
					for (var i = 0; i < files.length; i++) {

						UploadService.uploadFile(files[i]);

						// only upload one file
						break;
					}
				});

				elem.bind('paste', function(e) {
					e.preventDefault();

					var paste = {};
		
					if (/text\/plain|text\/html/.test(e.clipboardData.types)) {

						var blob = new Blob([e.clipboardData.getData('text/plain')], {type : 'text/plain'});						
						UploadService.uploadFile(blob);
		
					} else if (/Files/.test(e.clipboardData.types)) {
		
						for (var i = 0; i < e.clipboardData.items.length; i++) {
							if (e.clipboardData.items[i].kind == 'file' && e.clipboardData.items[i].type == 'image/png') {

								var imageFile = e.clipboardData.items[i].getAsFile();
								UploadService.uploadFile(imageFile);
			
								// only paste one file
								break;
							}
						}
					}
				});
			}
		};
	});

window.addEventListener('load', function() {
    FastClick.attach(document.body);
}, false);
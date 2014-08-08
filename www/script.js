
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

		$rootScope.$on('upload-error', function(e, msg) {
			$rootScope.safeApply(function($scope) {
				$scope.error = msg ? msg : 'Could not upload file';
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
				$rootScope.$broadcast('upload-error', 'File too big');
			} else {
				up.upload({}, d, function(f) {
					if (f.uid) {
						$rootScope.$broadcast('uploaded', f);
					} else {
						$rootScope.$broadcast('upload-error', f);
					}
				}, function() {
					$rootScope.$broadcast('upload-error', '500');
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
					$rootScope.$broadcast('upload-error', 'Unsupported file type');
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

			fileReader.onerror = function(e) {
				$rootScope.$broadcast('upload-error', 'Could not read file');
			};
			
			fileReader.readAsDataURL(file);			
		}
	})

	.controller('Home', function ($rootScope) {
		$rootScope.showBack = false;
		
		$rootScope.fileReader = window.FileReader ? 'yes' : 'no';
		$rootScope.formData = window.FormData ? 'yes' : 'no';
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
					var file = (e.target.files || e.files)[0];
					UploadService.uploadFile(file);
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


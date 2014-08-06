

angular.module('BeerSquirrel', ['ngRoute'])
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

	.controller('Home', function ($scope) {
		
	})

	.controller( 'View', function ($scope, $http, $routeParams) {
		$http.get('/get/' + $routeParams.id).success(function(file) {
			$scope.file = file;
		});
	})

	.directive('uploadPaste', function($http, $location){ 
		return {
			restrict: 'A',
			link: function(scope, elem, attr, ctrl) {
				elem.bind('paste', function(e) {

					var uploadPaste = function(paste) {

						$http({
							method: 'POST',
							url: '/upload',
							data: paste
						}).success(function(data) {
							$location.path('/view/' + data.uid);
							console.log(data);
						});
						// 2MB
						if (paste.data.length > 2097152) {
							
						}
						console.log(paste);
					};
		
					var paste = {
		
					};
		
					if (/text\/html/.test(e.clipboardData.types)) {
						//paste.data = e.clipboardData.getData('text/html');
						//paste.type = 'html';
						paste.data = e.clipboardData.getData('text/plain');
						paste.type = 'txt';
		
					} else if (/text\/plain/.test(e.clipboardData.types)) {
						paste.data = e.clipboardData.getData('text/plain');
						paste.type = 'txt';
		
					} else if (/Files/.test(e.clipboardData.types)) {
		
						for (var i = 0; i < e.clipboardData.items.length; i++) {
							if (e.clipboardData.items[i].kind == 'file' && e.clipboardData.items[i].type == 'image/png') {
		
								var imageFile = e.clipboardData.items[i].getAsFile();
		
								var fileReader = new FileReader();
								fileReader.onloadend = function(e) {
									paste.type = 'image';
									paste.data = this.result;
									uploadPaste(paste);
								};
			
								// TODO: Error Handling!
								// fileReader.onerror = ...
			
								fileReader.readAsDataURL(imageFile);
			
								// prevent the default paste action
								e.preventDefault();
			
								// only paste 1 image at a time
								break;
							}
						}
					}
					if (paste.type) {
						uploadPaste(paste);
					}
		

				});
			}
		};
	});


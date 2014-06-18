

angular.module('BeerSquirrel', ['ngRoute'])
	.config(function($routeProvider, $locationProvider){
		$routeProvider
			.when('/', {
				action: 'home',
				controller: 'Home',
				templateUrl: 'home.html'
			})
			.when('/beer', {
				action: 'beer',
				controller: 'Beer',
				templateUrl: 'beer.html'
			})
			.otherwise({
				redirectTo: '/'
			});

		$locationProvider.html5Mode(true);
	})

	.controller('Home', ['$scope', function ($scope) {
		$scope.greetMe = 'home';
	}])

	.controller('Beer', ['$scope', function ($scope) {
		$scope.greetMe = 'beer';
	}])

	.directive('uploadPaste', function(){ 
		return {
			restrict: 'A',
			link: function(scope, elem, attr, ctrl) {
				elem.bind('paste', function(e) {

					var uploadPaste = function(paste) {
						if (paste.data.length > 1048576) {
							
						}
						console.log(paste);
					};
		
					var paste = {
		
					};
		
					if (/text\/html/.test(e.clipboardData.types)) {
						paste.data = e.clipboardData.getData('text/html');
						paste.type = 'html';
		
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


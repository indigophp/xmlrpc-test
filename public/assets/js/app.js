var xmlrpcTest = angular.module('xmlrpcTest', ['ngResource']);

xmlrpcTest.factory('Method', ['$resource',
	function($resource) {
		return {
			List: $resource('listMethods/', {}, {
				query: {
					method: 'GET',
					isArray: true
				}
			}),
			Info: $resource('getMethodInfo/:methodName', {}),
			Call: $resource('call/:methodName', {}),
		};
}]);

xmlrpcTest.controller('XmlrpcCtrl', ['$scope', 'Method', function($scope, Method) {
	var customMethod = {
		'name': 'custom',
		'longName': 'Custom method...',
		'description': 'Not available',
		'signature': [],
		'returnType': 'unknown'
	};

	$scope.methods = [customMethod];
	$scope.method = customMethod;

	$scope.json = '{}';

	$scope.listMethods = function() {
		Method.List.query({
			uri: $scope.uri
		}, function(response) {
			response.unshift(customMethod);
			$scope.methods = response;
			$scope.method = customMethod;
		});
	};

	$scope.methodChanged = function() {
		if ($scope.method.description == undefined || $scope.method.signature == undefined || $scope.method.returnType == undefined) {
			Method.Info.get({
				methodName: $scope.method.name,
				uri: $scope.uri
			}, function(response) {
				$scope.method.description = response.description;
				$scope.method.signature = response.signature;
				$scope.method.returnType = response.returnType;
			});
		}

		$scope.response = '';
	}

	$scope.send = function() {
		methodName = $scope.method.name;

		if ($scope.method.name == 'custom') {
			methodName = $scope.methodNameOverride;
		}

		Method.Call.get({
			methodName: methodName,
			params: $scope.json,
			uri: $scope.uri
		}, function(response) {
			$scope.response = response;
		});
	}
}]);

xmlrpcTest.directive('jsoneditor', function($parse) {
	return {
		restrict: 'E',
		replace: true,
		transclude: false,
		require: 'ngModel',
		compile: function(element, attrs) {
			var modelAccessor = $parse(attrs.ngModel);

			var html = '<div id="' + attrs.id + '"></div>';
			var newElem = $(html);
			element.replaceWith(newElem);

			return function (scope, element, attrs, controller) {
				var editor = new JSONEditor(element.get(0), {
					change: function() {
						var json = editor.getText();

						scope.$apply(function(scope) {
							modelAccessor.assign(scope, json);
						});
					},
					modes: ['code', 'form', 'text', 'tree', 'view']
				}, {});

				scope.$watch(modelAccessor, function(val) {
					// if (!scope.$$phase && !scope.$root.$$phase) {
					if (!scope.$$phase) {
						editor.setText(val);
					}
				});
			};
		}
	};
});

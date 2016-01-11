'use strict';

/* Controllers */

var monshopControllers = angular.module('monshopControllers', []);


monshopControllers.controller("NavCtrl",function($scope,$http){
	var serviceBase = 'api/v1/';
	$http.get(serviceBase + 'categories').then(function (results) {
        $scope.categories = results.data;
    });
});

monshopControllers.controller('ProductListCtrl', ['$scope', '$routeParams', 'Product',
  function($scope, $routeParams, Product) {
    $scope.products = Product.query({categoryId: $routeParams.categoryId});
    $scope.orderProp = 'age';
  }]);


monshopControllers.controller('ProductDetailCtrl', ['$scope', '$routeParams', 'Product',
  function($scope, $routeParams, Product) {
    $scope.product = Product.get({categoryId: $routeParams.categoryId, 
        productId: $routeParams.productId}, function(product) {
      $scope.mainImageUrl = product.images[0];
    });

    $scope.setImage = function(imageUrl) {
      $scope.mainImageUrl = imageUrl;
    };
  }]);


monshopControllers.controller('authCtrl', function ($scope, $rootScope, $routeParams, $location, $http, Data) {
    //initially set those objects to null to avoid undefined error
    $scope.login = {};
    $scope.signup = {};
    $scope.doLogin = function (customer) {
        Data.post('login', {
            customer: customer
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {
                $location.path('dashboard');
            }
        });
    };
    $scope.signup = {email:'',password:'',name:'',phone:'',address:''};
    $scope.signUp = function (customer) {
        Data.post('signup', {
            customer: customer
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {
                $location.path('dashboard');
            }
        });
    };
    $scope.logout = function () {
        Data.get('logout').then(function (results) {
            Data.toast(results);
            $location.path('login');
        });
    }
});

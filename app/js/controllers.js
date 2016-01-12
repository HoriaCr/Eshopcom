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
});

monshopControllers.controller('logoutCtrl',  function ($scope, $rootScope, $routeParams, $location, $http, Data) {
     Data.get('logout').then(function (results) {
            Data.toast(results);
            $location.path('login');
    });
});


monshopControllers.controller("cartCtrl", function($scope, $http) {
	var serviceBase = 'api/v1/';
    $scope.getTotal = function(){
        var total = 0;
        for(var i = 0; i < $scope.cart.length; i++){
            var product = $scope.cart[i];
            total += (product.price * product.quantity);
        }
        return total;
    };
    
    $scope.addToCart = function(product) {
        $http.post(serviceBase + 'addtocart', {product: product
        }).then(function(results) {

        });
    };

    $scope.removeFromCart = function(product) {
        $http.post(serviceBase + 'removefromcart', {product: product
        }).then(function(results) {
            window.location.reload();
        });
        
    };

	$http.get(serviceBase + 'cart').then(function (results) {
        $scope.cart = results.data;
    });
});


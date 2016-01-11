'use strict';

/* App Module */

var monshopApp = angular.module('monshopApp', [
  'ngRoute',
  'monshopAnimations',

  'monshopControllers',
  'monshopFilters',
  'monshopServices'
]);

monshopApp.config(['$routeProvider',
  function($routeProvider) {
    $routeProvider
     .when('/categories/:categoryId/:productId', {
        templateUrl: 'partials/product-detail.html',
        controller: 'ProductDetailCtrl'
     })
     .when('/categories/:categoryId', {
        templateUrl: 'partials/product-list.html',
        controller: 'ProductListCtrl'
     })
     .when('/login', {
        controller: 'LoginCtrl',
        templateUrl: 'partials/login.html',
      })
     .when('/signup', {
        controller: 'SignupCtrl',
        templateUrl: 'partials/signup.html',
      })
      .otherwise({
        redirectTo: '/categories/phones'
      });
  }]);

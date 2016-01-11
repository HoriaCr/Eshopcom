'use strict';

/* App Module */

var monshopApp = angular.module('monshopApp', [
  'ngRoute',
  'toaster',
  'monshopAnimations',

  'monshopControllers',
  'monshopDirectives',
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
        title: 'Login',
        templateUrl: 'partials/login.html',
        controller: 'authCtrl'
     })
     .when('/logout', {
        title: 'Logout',
        templateUrl: 'partials/login.html',
        controller: 'logoutCtrl'
     })
     .when('/signup', {
        title: 'Signup',
        templateUrl: 'partials/signup.html',
        controller: 'authCtrl'
     })
     .when('/dashboard', {
        title: 'Dashboard',
        templateUrl: 'partials/dashboard.html',
        controller: 'authCtrl'
     })
    .otherwise({
        redirectTo: '/categories/phones'
      });
  }]).run(function ($rootScope, $location, Data) {
        $rootScope.$on("$routeChangeStart", function (event, next, current) {
            $rootScope.authenticated = false;
            Data.get('session').then(function (results) {
                if (results.uid) {
                    $rootScope.authenticated = true;
                    $rootScope.uid = results.uid;
                    $rootScope.name = results.name;
                    $rootScope.email = results.email;
                } else {
                    var nextUrl = next.$$route.originalPath;
                    if (nextUrl == '/signup' || nextUrl == '/login') {

                    } else {
                        $location.path("/login");
                    }
                }
            });
        });
});

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
        controller: 'LoginController',
        templateUrl: 'partials/login.html',
        controllerAs: 'vm'
      })
     .when('/register', {
        controller: 'RegisterController',
        templateUrl: 'partials/register.html',
        controllerAs: 'vm'
      })
      .otherwise({
        redirectTo: '/categories/phones'
      });
  }]);


run.$inject = ['$rootScope', '$location', '$cookieStore', '$http'];
function run($rootScope, $location, $cookieStore, $http) {
    // keep user logged in after page refresh
    $rootScope.globals = $cookieStore.get('globals') || {};
    if ($rootScope.globals.currentUser) {
        $http.defaults.headers.common['Authorization'] = 'Basic ' + $rootScope.globals.currentUser.authdata; // jshint ignore:line
    }

    $rootScope.$on('$locationChangeStart', function (event, next, current) {
        // redirect to login page if not logged in and trying to access a restricted page
        var restrictedPage = $.inArray($location.path(), ['/login', '/register']) === -1;
        var loggedIn = $rootScope.globals.currentUser;
        if (restrictedPage && !loggedIn) {
            $location.path('/login');
        }
    });
}

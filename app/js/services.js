'use strict';

/* Services */

var monshopServices = angular.module('monshopServices', ['ngResource']);

monshopServices.factory('Product', ['$resource',
  function($resource){
    return $resource('api/v1/categories/:categoryId/:productId', {}, {
        query: {method:'GET', params: {productId: ''}, isArray:true}
  });
}]);


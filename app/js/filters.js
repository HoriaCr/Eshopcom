'use strict';

/* Filters */

angular.module('monshopFilters', []).filter('checkmark', function() {
  return function(input) {
    return input ? '\u2713' : '\u2718';
  };
});

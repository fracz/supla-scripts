angular.module('supla-scripts').filter 'appUrl', ($location) ->
  (suffix) ->
    $location.absUrl().replace($location.url(), '') + suffix

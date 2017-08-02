angular.module('supla-scripts').filter 'thermostatPreviewUrl', ($location) ->
  (slug) ->
    $location.absUrl().replace($location.url(), '') + '/thermostat-preview/' + slug

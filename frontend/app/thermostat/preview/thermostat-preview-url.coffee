angular.module('supla-scripts').filter 'thermostatPreviewUrl', (appUrlFilter) ->
  (slug) ->
    appUrlFilter('/thermostat-preview/' + slug)

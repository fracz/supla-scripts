angular.module('supla-scripts').component 'thermostatPreview',
  templateUrl: 'app/thermostat/preview/thermostat-preview.html'
  controller: (Thermostats, ScopeInterval, $scope) ->
    new class
      $onInit: ->
        @fetch()
        ScopeInterval($scope, @fetch, 15000, 5000)

      fetch: =>
        Thermostats.one('default').get().then((@thermostat) =>)

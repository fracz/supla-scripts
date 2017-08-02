angular.module('supla-scripts').component 'thermostatPreview',
  templateUrl: 'app/thermostat/preview/thermostat-preview.html'
  controller: (Thermostats, ScopeInterval, $scope, $state) ->
    new class
      $onInit: ->
        @fetch()
        ScopeInterval($scope, @fetch, 15000, 5000)

      fetch: =>
        Thermostats.one('default').withHttpConfig(skipErrorHandler: yes).get()
        .then((@thermostat) =>)
        .catch (response) ->
          if response.status is 404
            $state.go('^.profiles')

      toggleEnabled: ->
        @thermostat.patch(enabled: @thermostat.enabled).then((@thermostat) =>)

      updateActiveProfile: ->
        @thermostat.patch(activeProfileId: @thermostat.activeProfile.id).then((@thermostat) =>)

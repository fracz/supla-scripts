angular.module('supla-scripts').component 'thermostatPreview',
  templateUrl: 'app/thermostat/preview/thermostat-preview.html'
  bindings:
    slug: '<'
  controller: (Thermostats, ScopeInterval, $scope, $state) ->
    new class
      $onInit: ->
        @fetch()
        ScopeInterval($scope, @fetch, 15000, 5000)

      fetch: =>
        endpoint = Thermostats.one('default').withHttpConfig(skipErrorHandler: yes)
        endpoint = Thermostats.one('preview').one(@slug) if @slug
        endpoint.get()
        .then(@receiveThermostat)
        .catch (response) =>
          if response.status is 404 and not @slug
            $state.go('^.profiles')

      receiveThermostat: (@thermostat) =>

      toggleEnabled: ->
        @thermostat.patch(enabled: @thermostat.enabled).then(@receiveThermostat)

      updateActiveProfile: ->
        @thermostat.patch(activeProfileId: @thermostat.activeProfile.id).then(@receiveThermostat)

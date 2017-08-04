angular.module('supla-scripts').component 'thermostatPreview',
  templateUrl: 'app/thermostat/preview/thermostat-preview.html'
  bindings:
    slug: '<'
  controller: (Thermostats, ScopeInterval, $scope, $state) ->
    new class
      intervalPromise: null
      changingPromise: null

      $onInit: ->
        @fetch()
        ScopeInterval($scope, @fetch, 15000, 5000)

      fetch: =>
        if not @changingPromise
          endpoint = Thermostats.one('default').withHttpConfig(skipErrorHandler: yes)
          endpoint = Thermostats.one('preview').one(@slug) if @slug
          @intervalPromise = endpoint.get()
            .then(@receiveThermostat)
            .catch (response) =>
              if response.status is 404 and not @slug
                $state.go('^.profiles')

      changeStateManually: (request) =>
        if not @changingPromise
          @changingPromise = @intervalPromise.then =>
            request().finally(=> @changingPromise = undefined)

      receiveThermostat: (@thermostat) =>

      toggleEnabled: ->
        @changeStateManually =>
          @thermostat.patch(enabled: @thermostat.enabled).then(@receiveThermostat)

      updateActiveProfile: ->
        @changeStateManually =>
          @thermostat.patch(activeProfileId: @thermostat.activeProfile.id).then(@receiveThermostat)

      setRoomAction: (room, action) ->
        @changeStateManually =>
          @thermostat.patch(roomAction: {roomId: room.id, action}).then(@receiveThermostat)

      clearRoomAction: (room) ->
        @changeStateManually =>
          @thermostat.patch(roomAction: {roomId: room.id, clear: yes}).then(@receiveThermostat)

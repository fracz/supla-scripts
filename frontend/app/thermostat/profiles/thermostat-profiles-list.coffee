angular.module('supla-scripts').component 'thermostatProfilesList',
  templateUrl: 'app/thermostat/profiles/thermostat-profiles-list.html'
  bindings:
    thermostat: '<'
  controller: ($state) ->
    new class
      $onInit: ->
        @thermostat.all('thermostat-rooms').getList().then (@rooms) =>
          if not @rooms.length
            $state.go('^.rooms')
        @thermostat.all('thermostat-profiles').getList().then (@profiles) =>
          @adding = true if not @profiles.length

      addNewProfile: (profile) ->
        @thermostat.all('thermostat-profiles').post(profile).then (savedProfile) =>
          @adding = false
          @profiles.push(savedProfile)

      saveProfile: (profile, newData) ->
        angular.extend(profile, newData)
        profile.put().then ->
          profile.editing = false

      deleteProfile: (profile) ->
        profile.remove().then =>
          @profiles.splice(@profiles.indexOf(profile), 1)

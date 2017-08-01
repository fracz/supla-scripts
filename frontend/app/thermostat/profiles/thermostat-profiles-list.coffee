angular.module('supla-scripts').component 'thermostatProfilesList',
  templateUrl: 'app/thermostat/profiles/thermostat-profiles-list.html'
  controller: (ThermostatRooms, ThermostatProfiles) ->
    new class
      $onInit: ->
        ThermostatRooms.getList().then((@rooms) =>)
        ThermostatProfiles.getList().then (@profiles) =>
          @adding = true if not @profiles.length

      addNewProfile: (profile) ->
        ThermostatProfiles.post(profile).then (savedProfile) =>
          @adding = false
          @profiles.push(savedProfile)

      saveProfile: (profile, newData) ->
        angular.extend(profile, newData)
        profile.put().then ->
          profile.editing = false

      deleteProfile: (profile) ->
        profile.remove().then =>
          @profiles.splice(@profiles.indexOf(profile), 1)

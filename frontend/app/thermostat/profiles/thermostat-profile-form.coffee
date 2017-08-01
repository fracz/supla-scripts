angular.module('supla-scripts').component 'thermostatProfileForm',
  templateUrl: 'app/thermostat/profiles/thermostat-profile-form.html'
  bindings:
    profile: '<'
    onSubmit: '&'
    onCancel: '&'
  controller: (ThermostatRooms) ->
    new class
      $onInit: ->
        ThermostatRooms.getList().then((@rooms) =>)
        if @profile
          @profile = angular.copy(@profile.plain())
        else
          @profile = {}

angular.module('supla-scripts').component 'thermostatProfileForm',
  templateUrl: 'app/thermostat/profiles/thermostat-profile-form.html'
  bindings:
    profile: '<'
    rooms: '<'
    onSubmit: '&'
    onCancel: '&'
  controller: ->
    new class
      $onInit: ->
        if @profile
          @profile = angular.copy(@profile.plain())
        else
          @profile = {}
        @profile.activeOn ?= []

angular.module('supla-scripts').component 'thermostatManualProfileTimeChange',
  templateUrl: 'app/thermostat/preview/thermostat-manual-profile-time-change.html'
  bindings:
    thermostat: '<'
    onTimeChange: '&'
  controller: (swangular, $scope) ->
    new class
      changeProfileTime: ->
        @nextProfileChange = moment(@thermostat.nextProfileChange)
        @now = moment()
        @timeChooseModal = swangular.open
          title: "Do kiedy ma być aktywny obecny profil?"
          htmlTemplate: 'app/thermostat/preview/thermostat-manual-profile-time-change-times.html'
          scope: $scope
          showCancelButton: yes
          showConfirmButton: yes
          cancelButtonText: 'Anuluj'
        .then(@apply)

      adjustTime: (diff) ->
        @nextProfileChange.add(diff, 'minutes')

      apply: =>
        swangular.closeModal(@timeChooseModal)
        @onTimeChange(time: @nextProfileChange.toDate())

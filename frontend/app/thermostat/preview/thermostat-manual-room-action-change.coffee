angular.module('supla-scripts').component 'thermostatManualRoomActionChange',
  templateUrl: 'app/thermostat/preview/thermostat-manual-room-action-change.html'
  bindings:
    room: '<'
    roomState: '<'
    onActionChange: '&'
  controller: (swangular, $scope) ->
    new class
      $onChanges: ->
        @cooling = @roomState?.action == 'cooling'
        @heating = @roomState?.action == 'heating'

      onChanged: (what) ->
        if what == 'cooling'
          @heating = off
        else
          @cooling = off
        action = if @[what] then what else null
        actionName = if action then 'włączyć' else 'wyłączyć'
        timeChooseModal = swangular.open
          title: "Na jak długo #{actionName}?"
          htmlTemplate: 'app/thermostat/preview/thermostat-manual-room-action-change-times.html'
          scope: $scope
          showCancelButton: yes
          showConfirmButton: no
          cancelButtonText: 'Anuluj'
        $scope.chooseTime = (time) =>
          swangular.closeModal(timeChooseModal)
          @onActionChange({action, time})
        return

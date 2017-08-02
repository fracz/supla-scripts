angular.module('supla-scripts').component 'thermostatManualRoomActionChange',
  templateUrl: 'app/thermostat/preview/thermostat-manual-room-action-change.html'
  bindings:
    room: '<'
    roomState: '<'
    onActionChange: '&'
  controller: class
    $onChanges: ->
      @cooling = @roomState.action == 'cooling'
      @heating = @roomState.action == 'heating'

    onChanged: (what) ->
      if what == 'cooling'
        @heating = off
      else
        @cooling = off
      action = if @[what] then what else null
      @onActionChange(action: action)

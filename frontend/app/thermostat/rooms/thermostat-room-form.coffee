angular.module('supla-scripts').component 'thermostatRoomForm',
  templateUrl: 'app/thermostat/rooms/thermostat-room-form.html'
  bindings:
    thermostat: '<'
    room: '<'
    onSubmit: '&'
    onCancel: '&'
  controller: class
    $onInit: ->
      if @room
        @room = angular.copy(@room.plain())
      else
        @room =
          thermometers: []
          heaters: []
          coolers: []
      @allowedSensorFunctions = ['HUMIDITYANDTEMPERATURE']
      @allowedSensorFunctions.push('THERMOMETER') if @thermostat.target is 'temperature'

    onChannelAdd: (channelId, group) ->
      @room[group].push(channelId)

    removeChannel: (channelId, group) ->
      @room[group].splice(@room[group].indexOf(channelId), 1)

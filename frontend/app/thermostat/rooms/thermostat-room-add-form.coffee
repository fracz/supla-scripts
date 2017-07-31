angular.module('supla-scripts').component 'thermostatRoomAddForm',
  templateUrl: 'app/thermostat/rooms/thermostat-room-add-form.html'
  controller: class
    constructor: (@Channels, @ThermostatRooms) ->
      @newRoom =
        thermometers: []
        heaters: []
        coolers: []

    onChannelAdd: (channelId, group) ->
      @newRoom[group].push(channelId)

    removeChannel: (channelId, group) ->
      @newRoom[group].splice(@newRoom[group].indexOf(channelId), 1)

    addRoom: ->
      @ThermostatRooms.post(@newRoom).then (room) ->


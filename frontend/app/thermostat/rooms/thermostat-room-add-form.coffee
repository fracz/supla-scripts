angular.module('supla-scripts').component 'thermostatRoomAddForm',
  templateUrl: 'app/thermostat/rooms/thermostat-room-add-form.html'
  controller: class
    constructor: (@Channels) ->
      @newRoom =
        thermometers: []
        heaters: []
        coolers: []

    onChannelAdd: (channelId, group) ->
      @newRoom[group].push(channelId)

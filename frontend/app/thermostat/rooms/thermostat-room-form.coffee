angular.module('supla-scripts').component 'thermostatRoomForm',
  templateUrl: 'app/thermostat/rooms/thermostat-room-form.html'
  bindings:
    room: '<'
    onSubmit: '&'
    onCancel: '&'
  controller: class
    $onInit: ->
      if @room
        @room = angular.copy(@room.plain())
      else
        @room = {}
      
    onChannelAdd: (channelId, group) ->
      @room[group] ?= []
      @room[group].push(channelId)

    removeChannel: (channelId, group) ->
      @room[group].splice(@room[group].indexOf(channelId), 1)
      delete @room[group] if not @room[group].length

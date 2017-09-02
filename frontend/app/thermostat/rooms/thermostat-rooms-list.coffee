angular.module('supla-scripts').component 'thermostatRoomsList',
  templateUrl: 'app/thermostat/rooms/thermostat-rooms-list.html'
  bindings:
    thermostat: '<'
  controller: class
    $onInit: ->
      @thermostat.all('thermostat-rooms').getList().then (@rooms) =>
        @adding = true if not @rooms.length

    addNewRoom: (room) ->
      @thermostat.all('thermostat-rooms').post(room).then (savedRoom) =>
        @adding = false
        @rooms.push(savedRoom)

    saveRoom: (room, newData) ->
      angular.extend(room, newData)
      room.put().then ->
        room.editing = false

    deleteRoom: (room) ->
      room.remove().then =>
        @rooms.splice(@rooms.indexOf(room), 1)

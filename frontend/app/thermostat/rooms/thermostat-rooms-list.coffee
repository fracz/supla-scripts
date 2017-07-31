angular.module('supla-scripts').component 'thermostatRoomsList',
  templateUrl: 'app/thermostat/rooms/thermostat-rooms-list.html'
  controller: (ThermostatRooms) ->
    new class
      $onInit: ->
        ThermostatRooms.getList().then (@rooms) =>
          @adding = true if not @rooms.length

      addNewRoom: (room) ->
        ThermostatRooms.post(room).then (savedRoom) =>
          @adding = false
          @rooms.push(savedRoom)

      saveRoom: (room, newData) ->
        angular.extend(room, newData)
        room.put().then ->
          room.editing = false

      deleteRoom: (room) ->
        room.remove().then =>
          @rooms.splice(@rooms.indexOf(room), 1)

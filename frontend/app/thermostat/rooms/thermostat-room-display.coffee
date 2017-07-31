angular.module('supla-scripts').component 'thermostatRoomDisplay',
  templateUrl: 'app/thermostat/rooms/thermostat-room-display.html'
  bindings:
    room: '<'
    onEdit: '&'
    onDelete: '&'

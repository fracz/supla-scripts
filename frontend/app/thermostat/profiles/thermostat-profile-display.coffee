angular.module('supla-scripts').component 'thermostatProfileDisplay',
  templateUrl: 'app/thermostat/profiles/thermostat-profile-display.html'
  bindings:
    profile: '<'
    rooms: '<'
    onEdit: '&'
    onDelete: '&'

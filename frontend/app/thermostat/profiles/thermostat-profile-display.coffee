angular.module('supla-scripts').component 'thermostatProfileDisplay',
  templateUrl: 'app/thermostat/profiles/thermostat-profile-display.html'
  bindings:
    thermostat: '<'
    profile: '<'
    rooms: '<'
    onEdit: '&'
    onDelete: '&'

angular.module('supla-scripts').filter 'thermostatUnit', (byThermostatTargetFilter) ->
  (thermostat) ->
    byThermostatTargetFilter(thermostat, '°C', '%')

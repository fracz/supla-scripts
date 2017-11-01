angular.module('supla-scripts').filter 'byThermostatTarget', ->
  (thermostat, textIfTemperature, textIfHumidity) ->
    if thermostat?.target is 'humidity' then textIfHumidity else textIfTemperature

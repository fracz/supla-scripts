angular.module('supla-scripts').component 'thermostatsList',
  templateUrl: 'app/thermostat/thermostats-list.html'
  controller: (Thermostats) ->
    new class
      $onInit: ->
        Thermostats.getList().then((@thermostats) =>)

      createNew: ->
        Thermostats.post(label: @newLabel).then (thermostat) =>
          @newLabel = ''
          @thermostats.push(thermostat)

      disableThermostat: (thermostat) ->
        thermostat.patch(enabled: false).then((updated) => angular.extend(thermostat, updated))

      enableThermostat: (thermostat) ->
        thermostat.patch(enabled: true).then((updated) => angular.extend(thermostat, updated))

      deleteThermostat: (thermostat) ->
        thermostat.remove().then =>
          @thermostats.splice(@thermostats.indexOf(thermostat), 1)

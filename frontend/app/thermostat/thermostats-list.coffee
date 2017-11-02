angular.module('supla-scripts').component 'thermostatsList',
  templateUrl: 'app/thermostat/thermostats-list.html'
  controller: (Thermostats, $state) ->
    new class
      $onInit: ->
        @newTarget = 'temperature'
        Thermostats.getList().then((@thermostats) =>)

      createNew: ->
        Thermostats.post({label: @newLabel, target: @newTarget}).then (thermostat) =>
          @newLabel = ''
          @newTarget = 'temperature'
          @thermostats.push(thermostat)
          $state.go('thermostat.rooms', {id: thermostat.id})

      disableThermostat: (thermostat) ->
        thermostat.patch(enabled: false).then((updated) => angular.extend(thermostat, updated))

      enableThermostat: (thermostat) ->
        thermostat.patch(enabled: true).then((updated) => angular.extend(thermostat, updated))

      deleteThermostat: (thermostat) ->
        thermostat.remove().then =>
          @thermostats.splice(@thermostats.indexOf(thermostat), 1)

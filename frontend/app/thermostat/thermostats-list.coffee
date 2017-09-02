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

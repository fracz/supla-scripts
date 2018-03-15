angular.module('supla-scripts').component 'thermostatSelector',
  templateUrl: 'app/thermostat/thermostat-selector.html'
  bindings:
    hideIds: '<'
  require:
    ngModel: 'ngModel'
  controller: class
    constructor: (@Thermostats) ->

    $onInit: =>
      @Thermostats.getList().then (@thermostats) =>
        @ngModel.$render = => @chosenThermostatId = @ngModel.$viewValue

    isNotHidden: (thermostat) =>
      thermostat.id not in (@hideIds or [])

    updateModel: ->
      @ngModel.$setViewValue(@chosenThermostatId)

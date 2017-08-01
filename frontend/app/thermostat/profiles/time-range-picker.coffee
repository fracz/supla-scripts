angular.module('supla-scripts').component 'timeRangePicker',
  templateUrl: 'app/thermostat/profiles/time-range-picker.html'
  require:
    ngModel: 'ngModel'
  controller: class
    constructor: (minutesToHumanTimeFilter) ->

      @sliderOptions =
        floor: 0
        ceil: 24 * 60
        step: 5
        minRange: 15
        translate: minutesToHumanTimeFilter
        onChange: =>
          @ngModel.$setViewValue(@timeRange)

    $onInit: ->
      @timeRange =
        timeStart: 0
        timeEnd: 24 * 60
      @ngModel.$render = => angular.extend(@timeRange, @ngModel.$viewValue)

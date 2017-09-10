angular.module('supla-scripts').component 'timeRangePicker',
  templateUrl: 'app/thermostat/profiles/time-range-picker.html'
  require:
    ngModel: 'ngModel'
  controller: (minutesToHumanTimeFilter, humanTimeToMinutesFilter) ->
    new class
      constructor: ->
        @sliderOptions =
          floor: 0
          ceil: 24 * 60
          step: 5
          minRange: 15
          translate: minutesToHumanTimeFilter
          onChange: @updateNgModel

      updateNgModel: =>
        @ngModel.$setViewValue
          timeStart: moment().hours(0).minutes(@timeRange.timeStart).seconds(0).format()
          timeEnd: moment().hours(0).minutes(@timeRange.timeEnd).seconds(0).format()

      $onInit: ->
        @timeRange =
          timeStart: 0
          timeEnd: 24 * 60
        @ngModel.$render = =>
          if @ngModel.$viewValue
            angular.extend @timeRange,
              timeStart: humanTimeToMinutesFilter(moment(@ngModel.$viewValue.timeStart).format('H:m'))
              timeEnd: humanTimeToMinutesFilter(moment(@ngModel.$viewValue.timeEnd).format('H:m')) || 1440
          else
            @updateNgModel()

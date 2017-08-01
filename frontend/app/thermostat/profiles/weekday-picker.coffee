###
  0 - Sunday, 1 - Monday, ..., 6 - Saturday
###
angular.module('supla-scripts').component 'weekdayPicker',
  require:
    ngModel: 'ngModel'
  templateUrl: 'app/thermostat/profiles/weekday-picker.html'
  controller: class
    constructor: ->
      @weekdays = {}
      @weekdays[day] = off for day in [0..6]

    $onInit: ->
      @ngModel.$render = => @weekdays[+day] = on for day in @ngModel.$viewValue if @ngModel.$viewValue?.length

    $onChanges: (changes) =>

    updateWeekdays: =>
      weekdays = (+weekday for weekday, active of @weekdays when active)
      @ngModel.$setViewValue(weekdays)

angular.module('supla-scripts').component 'dateRangePicker',
  templateUrl: 'app/temperature-history/date-range-picker/date-range-picker.html'
  bindings:
    caption: '@'
    clearable: '<'
  require:
    ngModel: 'ngModel'
  controller: ($element, $scope) ->
    new class
      $onInit: ->
        @dateRange ?= {}
        @ngModel.$render = =>
          @dateRange = @ngModel.$viewValue
          @dateRange ?= {}
          @dateRangePicker.setStartDate(@dateRange.startDate)
          @dateRangePicker.setEndDate(@dateRange.endDate)
        @dateRangePickerElement = $element.find('.btn-daterangepicker')
        @dateRangePickerElement.daterangepicker
          timePicker: yes
          timePicker24Hour: yes
          maxDate: moment()
          minDate: moment('2016-01-01T00:00:00')
          opens: 'right'
          startDate: @dateRange.startDate
          endDate: @dateRange.endDate
          locale:
            applyLabel: "Ok"
            fromLabel: "Od"
            format: "DD.MM.YYYY"
            toLabel: "Do"
            cancelLabel: if @clearable then 'Wyczyść' else 'Anuluj'
            customRangeLabel: 'Wybierz'
          ranges:
            'Ostatnia godzina': [moment().subtract(1, 'hours'), moment()],
            'Ostatnie 6 godzin': [moment().subtract(6, 'hours'), moment()],
            'Ostatnie 24 godziny': [moment().subtract(24, 'hours'), moment()],
            'Dzisiaj': [moment().startOf('day'), moment()],
            'Wczoraj': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')]
            'Ostatnie 7 dni': [moment().subtract(6, 'days').startOf('day'), moment()]
            'Ostatnie 30 dni': [moment().subtract(29, 'days').startOf('day'), moment()]
            'Bieżący miesiąc': [moment().startOf('month'), moment().endOf('month')]
            'Ubiegły miesiąc': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        @dateRangePickerElement.on 'apply.daterangepicker', (e, picker) =>
          $scope.$apply =>
            @dateRange =
              startDate: picker.startDate
              endDate: picker.endDate
            @ngModel.$setViewValue(@dateRange)
        if @clearable
          @dateRangePickerElement.on 'cancel.daterangepicker', =>
            $scope.$apply =>
              @dateRange = {}
              @ngModel.$setViewValue(@dateRange)
        @dateRangePicker = @dateRangePickerElement.data('daterangepicker')

      updateDatetimepickerConfiguration: =>


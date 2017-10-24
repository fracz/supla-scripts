angular.module('supla-scripts').component 'dateWithTooltip',
  template: '<span tooltips tooltip-template="{{ $ctrl.date | amDateFormat: \'LL LTS\' }}" tooltip-side="left">{{ $ctrl.date | amCalendar }}</span>'
  bindings:
    date: '<'

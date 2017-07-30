angular.module('supla-scripts').directive 'valueOrNotGiven', ->
  restrict: 'A'
  scope:
    value: '=valueOrNotGiven'
    notGiven: '@'
  template: '<span ng-if="value">{{ value }}</span><em ng-else>{{ notGiven || "nie podano" }}</em>'

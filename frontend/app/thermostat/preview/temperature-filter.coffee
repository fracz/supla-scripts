angular.module('supla-scripts').filter 'temperature', (numberFilter) ->
  (value) ->
    numberFilter(value, 1) + '°C'

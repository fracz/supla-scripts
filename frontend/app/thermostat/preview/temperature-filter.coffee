angular.module('supla-scripts').filter 'temperature', (numberFilter) ->
  (value) ->
    if value == undefined then '?' else numberFilter(value, 1) + '°C'

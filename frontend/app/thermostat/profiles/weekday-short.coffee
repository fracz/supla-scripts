angular.module('supla-scripts').filter 'weekdayShort', ->
  (day) ->
    moment().day(day).format('ddd')

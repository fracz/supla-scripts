angular.module('supla-scripts').filter 'minutesToHumanTime', ->
  (minutes) ->
    minutes = 1439 if minutes >= 1440
    hours = minutes // 60
    minutes = Math.round(minutes % 60)
    minutes = '0' + minutes if minutes < 10
    "#{hours}:#{minutes}"

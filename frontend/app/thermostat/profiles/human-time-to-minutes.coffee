angular.module('supla-scripts').filter 'humanTimeToMinutes', ->
  (humanTime) ->
    parts = humanTime.split(':')
    parseInt(parts[0]) * 60 + parseInt(parts[1])

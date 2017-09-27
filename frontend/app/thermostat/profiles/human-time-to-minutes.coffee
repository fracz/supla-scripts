angular.module('supla-scripts').filter 'humanTimeToMinutes', ->
  (humanTime) ->
    parts = humanTime.split(':')
    Math.min(1439, parseInt(parts[0]) * 60 + parseInt(parts[1]))

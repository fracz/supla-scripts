angular.module('supla-scripts').filter 'deviceLabel', ->
  (device) ->
    device?.comment || device?.name

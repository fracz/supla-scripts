angular.module('supla-scripts').filter 'unixToDate', ->
  (unix) ->
    moment.unix(unix).toDate()

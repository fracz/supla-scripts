angular.module('supla-scripts').filter 'fromNow', ->
  (date, future = false) ->
    date = new Date() if future and moment().isAfter(date)
    moment(date).fromNow(future)

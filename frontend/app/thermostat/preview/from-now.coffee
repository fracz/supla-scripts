angular.module('supla-scripts').filter 'fromNow', ->
  (date, future = false) ->
    moment(date).fromNow(future)

angular.module('supla-scripts').filter 'isInNearFuture', ->
  (date, futureIsDays = 7) ->
    moment(date).diff(moment(), 'days') < futureIsDays

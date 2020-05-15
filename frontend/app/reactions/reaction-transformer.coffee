angular.module('supla-scripts').run (Restangular, Reactions) ->
  Restangular.extendModel Reactions.one('').route, (reaction) ->
    reaction

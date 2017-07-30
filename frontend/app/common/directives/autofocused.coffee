angular.module('supla-scripts').directive 'autofocused', ($timeout) ->
  restrict: 'A'
  scope:
    autofocused: '='
  link: (scope, element) ->
    focus = ->
      $timeout ->
        element[0].focus() if scope.autofocused
    scope.$watch('autofocused', focus)

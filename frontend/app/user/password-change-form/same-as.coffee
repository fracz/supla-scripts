# https://stackoverflow.com/a/18014975/878514
angular.module('supla-scripts').directive 'sameAs', ->
  restrict: 'A'
  require: 'ngModel'
  link: (scope, elem, attrs, ngModel) ->
    validate = ->
      val1 = ngModel.$viewValue
      val2 = attrs.sameAs
      ngModel.$setValidity('sameAs', !val1 || !val2 || val1 == val2)

    scope.$watch(attrs.ngModel, validate)
    attrs.$observe('sameAs', validate)

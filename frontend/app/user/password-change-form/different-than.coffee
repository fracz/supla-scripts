angular.module('supla-scripts').directive 'differentThan', ->
  restrict: 'A'
  require: 'ngModel'
  link: (scope, elem, attrs, ngModel) ->
    validate = ->
      val1 = ngModel.$viewValue
      val2 = attrs.differentThan
      ngModel.$setValidity('differentThan', !val1 || !val2 || val1 != val2)

    scope.$watch(attrs.ngModel, validate)
    attrs.$observe('differentThan', validate)

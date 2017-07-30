# source: http://stackoverflow.com/a/28431112/878514
angular.module('supla-scripts').directive 'validateEmail', ->
  EMAIL_REGEXP = /^[_a-z0-9]+(\.[_a-z0-9]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,5})$/
  require: 'ngModel'
  restrict: 'A'
  link: (scope, elm, attrs, ctrl) ->
    if ctrl && ctrl.$validators.email
      ctrl.$validators.email = (modelValue) ->
        ctrl.$isEmpty(modelValue) || EMAIL_REGEXP.test(modelValue)

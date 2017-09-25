angular.module('supla-scripts').component 'suplaApiCredentialsForm',
  templateUrl: 'app/user/api-credentials/supla-api-credentials-form.html'
  require:
    ngModel: 'ngModel'
  controller: class
    $onInit: ->
      @ngModel.$render = =>
        @model = @ngModel.$viewValue

    updateModel: ->
      @ngModel.$setViewValue(@model)

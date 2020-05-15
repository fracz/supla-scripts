angular.module('supla-scripts').component 'sceneNotificationForm',
  templateUrl: 'app/scenes/notifications/scene-notification-form.html'
  bindings:
    onDelete: '&'
  require:
    ngModel: 'ngModel'
  controller: ->
    new class
      $onInit: ->
        @ngModel.$render = =>
          @notification = (@ngModel.$viewValue or {})

      onChange: ->
        @ngModel.$setViewValue(angular.copy(@notification))

angular.module('supla-scripts').component 'sceneNotificationsField',
  templateUrl: 'app/scenes/notifications/scene-notifications-field.html'
  require:
    ngModel: 'ngModel'
  controller: ->
    new class
      $onInit: ->
        @ngModel.$render = =>
          @notifications = (@ngModel.$viewValue or [])

      onChange: ->
        @ngModel.$setViewValue(@notifications)

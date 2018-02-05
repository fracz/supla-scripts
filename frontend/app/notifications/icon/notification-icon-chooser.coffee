angular.module('supla-scripts').component 'notificationIconChooser',
  templateUrl: 'app/notifications/icon/notification-icon-chooser.html'
  require:
    ngModel: 'ngModel'
  controller: (NotificationIcons) ->
    new class
      $onInit: ->
        @icons = ({value, faName} for value, faName of NotificationIcons)
        @ngModel.$render = =>
          @icon = {value: @ngModel.$viewValue, faName: NotificationIcons[@ngModel.$viewValue]}
          @choose(@icons[0]) if not @icon.value

      choose: (@icon) ->
        @ngModel.$setViewValue(@icon.value)

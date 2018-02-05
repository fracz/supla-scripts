angular.module('supla-scripts').filter 'notificationIcon', (NotificationIcons) ->
  (iconValue) -> NotificationIcons[iconValue]

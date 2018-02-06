angular.module('supla-scripts').run (Restangular, Notifications) ->
  Restangular.extendModel Notifications.one('').route, (notification) ->
    if notification.id
      for boolField in ['sound', 'flash', 'cancellable', 'vibrate', 'ongoing', 'awake', 'displayIfDisconnected']
        notification[boolField] = !!notification[boolField]
    if !angular.isArray(notification.actions)
      notification.actions = []
    notification

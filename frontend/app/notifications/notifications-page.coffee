angular.module('supla-scripts').component 'notificationsPage',
  templateUrl: 'app/notifications/notifications-page.html'
  controller: (Notifications, $state) ->
    $onInit: ->
      Notifications.getList().then((@notifications) =>)

    addNewNotification: (notification) ->
      Notifications.post(notification).then (savedNotification) =>
        @notifications.push(savedNotification)
        $state.go('notifications.details', {id: savedNotification.id})

angular.module('supla-scripts').component 'notificationForm',
  templateUrl: 'app/notifications/form/notification-form.html'
  bindings:
    notification: '<'
    onSubmit: '&'
    onCancel: '&'
  controller: ->
    new class
      $onInit: ->
        if @notification
          @notification = angular.copy(@notification.plain?() or @notification)
        else
          @notification =
            intervals: '*/15 * * * *'
            minConditions: 1
            actions: {}
            cancellable: yes

      submit: ->
        savedNotification = angular.copy(@notification)
        @onSubmit({savedNotification})

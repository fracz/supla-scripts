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
          @notification.generateSlug = !!@notification.slug
        else
          @notification = {actions: {}}

      submit: ->
        savedNotification = angular.copy(@notification)
        @onSubmit({savedNotification})

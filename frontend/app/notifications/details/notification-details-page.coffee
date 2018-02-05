angular.module('supla-scripts').component 'notificationDetailsPage',
  templateUrl: 'app/notifications/details/notification-details-page.html'
  bindings:
    notification: '<'
  controller: (swangular, $scope, $state, Notifier) ->
    new class
      saveNotification: (newData) ->
        angular.extend(@notification, newData)
        @notification.put().then =>
          $state.go($state.current.name, {}, reload: yes)

      reloadNotification: ->
        @notification.get().then (notification) =>
          angular.extend(@notification, notification)

      deleteNotification: ->
        swangular.open
          scope: $scope
          type: 'question'
          title: 'Na pewno?'
          text: 'Czy chcesz usunąć to powiadomienie?'
          showCancelButton: yes
          showConfirmButton: yes
          cancelButtonText: 'Anuluj'
          confirmButtonText: 'Tak, usuń'
          confirmButtonColor: '#d62c1a'
          showLoaderOnConfirm: true
          preConfirm: => @notification.remove()
        .then =>
          Notifier.success('Powiadomienie zostało usunięte.')
          $state.go('notifications', {}, reload: yes)

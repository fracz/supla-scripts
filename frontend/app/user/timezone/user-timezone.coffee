angular.module('supla-scripts').component 'userTimezone',
  templateUrl: 'app/user/timezone/user-timezone.html'
  bindings:
    user: '<'
  controller: (Notifier) ->
    new class
      updateTimezone: ->
        @user.patch(timezone: @user.timezone).then ->
          Notifier.success('Twoja strefa czasowa została zmieniona')

angular.module('supla-scripts').component 'userAutomateSettings',
  templateUrl: 'app/user/automate/user-automate-settings.html'
  bindings:
    user: '<'
  controller: (Notifier) ->
    new class
      $onInit: ->
        @automate = {}

      updateSettings: ->
        @user.patch(automate: @automate).then (user) =>
          @automate = {}
          @user.hasAutomateCredentials = user.hasAutomateCredentials
          if @user.hasAutomateCredentials
            Notifier.success('Ustawienia zostały zapisane. Możesz przejść do dodawania urządzeń.')
          else
            Notifier.success('Ustawienia Automate Cloud zostały usunięte.')

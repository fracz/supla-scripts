angular.module('supla-scripts').component 'userAutomateSettings',
  templateUrl: 'app/user/automate/user-automate-settings.html'
  bindings:
    user: '<'
  controller: (Notifier, Clients) ->
    new class
      $onInit: ->
        @automate = {}
        @mode = if @user.hasAutomateCredentials then 'choose' else 'configure'

      updateSettings: ->
        @user.patch(automate: @automate).then (user) =>
          @automate = {}
          @user.hasAutomateCredentials = user.hasAutomateCredentials
          if @user.hasAutomateCredentials
            @mode = 'add'
            Notifier.success('Ustawienia zostały zapisane. Możesz teraz dodać nowe urządzenie.')
          else
            Notifier.success('Ustawienia Automate Cloud zostały usunięte.')

      connectDevice: ->
        Clients.post()

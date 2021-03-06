angular.module('supla-scripts').component 'pushoverCredentials',
  templateUrl: 'app/user/pushover-credentials/pushover-credentials.html'
  bindings:
    user: '<'
  controller: (Notifier, $state) ->
    new class
      $onInit: ->
        @pushoverCredentials = {}

      changePushoverCredentials: ->
        @user.patch(pushoverCredentials: @pushoverCredentials).then =>
          Notifier.success('Dane Pushover zostały zapisane', 'Testowe powiadomienie zostało wysłane.')
          $state.go('account.details')

      testPushover: ->
        @user.patch(testPushover: yes)
          .then(=> Notifier.success('Testowe powiadomienie wysłane'))
          .catch(=> Notifier.error('Nie udało się wysłać powiadmienia.', 'Podaj poprawne dane autentykacji w Pushover i spróbuj ponownie.'))

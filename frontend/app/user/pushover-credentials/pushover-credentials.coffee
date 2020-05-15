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
          Notifier.success('Dane Pushover zostały zmienione', 'Testowe powiadomienie zostało wysłane.')
          $state.go('account.details')

angular.module('supla-scripts').component 'userApiCredentials',
  templateUrl: 'app/user/api-credentials/user-api-credentials.html'
  bindings:
    user: '<'
  controller: (Notifier, $state) ->
    new class
      $onInit: ->

      changeApiCredentials: ->
        @user.patch(apiCredentials: @newApiCredentials).then =>
          Notifier.success('Dane dostępowe do SUPLA API zostały zmienione.')
          $state.go('account.details')

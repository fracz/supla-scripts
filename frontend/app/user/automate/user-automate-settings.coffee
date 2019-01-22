angular.module('supla-scripts').component 'userAutomateSettings',
  templateUrl: 'app/user/automate/user-automate-settings.html'
  bindings:
    user: '<'
  controller: (Notifier, Clients, ScopeInterval, $scope) ->
    new class
      $onInit: ->
        @createNewRegistrationCode()
        ScopeInterval($scope, @checkRegistration, 5000, 1000)

      createNewRegistrationCode: =>
        Clients.one('').all('registration-codes').post().then((@client) =>)

      checkRegistration: =>
        if @client.registrationCode
          Clients.one(@client.id).withHttpConfig(skipErrorHandler: yes).get()
            .then (client) =>
              @client = client if not client.registrationCode
            .catch(@createNewRegistrationCode)

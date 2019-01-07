angular.module('supla-scripts').component 'userAutomateSettings',
  templateUrl: 'app/user/automate/user-automate-settings.html'
  bindings:
    user: '<'
  controller: (Notifier, Clients) ->
    new class
      $onInit: ->
        Clients.post().then((@client) =>)

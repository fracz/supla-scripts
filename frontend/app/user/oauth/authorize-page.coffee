angular.module('supla-scripts').component 'oauthAuthorizePage',
  templateUrl: 'app/user/oauth/authorize-page.html'
  controller: (Token, $state, $stateParams) ->
    new class
      $onInit: ->
        Token.authenticate($stateParams.code).then =>
          $state.go('dashboard')

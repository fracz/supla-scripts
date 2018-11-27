angular.module('supla-scripts').component 'oauthAuthorizePage',
  templateUrl: 'app/user/oauth/authorize-page.html'
  controller: (Token, $state, $stateParams) ->
    new class
      $onInit: ->
        Token.authenticate({authCode: $stateParams.code, rememberMe: $stateParams.state == 'remember'})
          .then(=> $state.go('dashboard'))
          .catch((@errorResponse) => )

angular.module('supla-scripts').component 'registerView',
  templateUrl: 'app/user/register-form/register-view.html'
  controller: (@APP_VERSION, @$stateParams, Token, $state) ->
    if Token.hasUser()
      $state.go('home', {}, {replace: yes})

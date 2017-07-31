angular.module('supla-scripts').component 'loginView',
  templateUrl: 'app/user/login-form/login-view.html'
  controller: (@APP_VERSION, @$stateParams, Token, $state) ->
    if Token.hasUser()
      $state.go('home', {}, {replace: yes})

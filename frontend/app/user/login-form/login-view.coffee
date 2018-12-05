angular.module('supla-scripts').component 'loginView',
  templateUrl: 'app/user/login-form/login-view.html'
  controller: (APP_VERSION, $stateParams, Token, $state, $rootScope) ->
    new class
      $onInit: ->
        @version = APP_VERSION
        $rootScope.getAppConfig.then (@config) =>
          @mode = $stateParams.mode or (if @config.oAuthClientId then 'oauth' else 'password')
          if Token.hasUser()
            $state.go('home', {}, {replace: yes})

      changeMode: (mode) =>
        if mode == 'oauth' and not @config.oAuthClientId
          return
        @mode = mode
        $state.go('login', {mode}, {replace: yes})

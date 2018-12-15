angular.module('supla-scripts').component 'loginWithToken',
  templateUrl: 'app/user/login-form/login-with-token.html'
  controller: (Restangular, Token, $state) ->
    new class
      checkToken: ->
        @error = undefined
        @loading = true
        if @userData?.personalToken?.length > 10
          Restangular.one('tokens/personal').withHttpConfig(skipErrorHandler: yes).patch(token: @userData.personalToken)
            .then (response) =>
              @tokenInfo = response
            .catch (error) =>
              @error = error.data.message
              @userData.personalToken = ''
            .finally () =>
              @loading = false

      loginWithToken: ->
        Token.authenticate(@userData).then =>
          $state.go('dashboard') if $state.current.name is 'login'
        .catch((error) => @error = error?.data?.message or 'Problem z logowaniem')

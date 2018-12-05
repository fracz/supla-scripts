angular.module('supla-scripts').component 'loginWithToken',
  templateUrl: 'app/user/login-form/login-with-token.html'
  controller: (Restangular) ->
    new class
      checkToken: ->
        @error = undefined
        @loading = true
        if @token?.length > 10
          Restangular.one('tokens/personal').withHttpConfig(skipErrorHandler: yes).patch(token: @token)
            .then (response) =>
              @tokenInfo = response
            .catch (error) =>
              @error = error.data.message
            .finally () =>
              @loading = false

angular.module('supla-scripts').component 'loginForm',
  templateUrl: 'app/user/login-form/login-form.html'
  controller: class
    constructor: (@Token, @$state) ->

    login: ->
      @error = false
      @Token.authenticate(@userData).then =>
        @$state.go('dashboard') if @$state.current.name is 'login'
      .catch(=> @error = true)

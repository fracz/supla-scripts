angular.module('supla-scripts').component 'loginForm',
  templateUrl: 'app/user/login-form/login-form.html'
  controller: class
    constructor: (@Token, @$state) ->
      if @Token.hasUser()
        @$state.go('home', {}, {replace: yes})

    login: ->
      @error = false
      @Token.authenticate(@userData).then ->
        @$state.go('dashboard') if @$state.current.name is 'login'
      .catch(=> @error = true)

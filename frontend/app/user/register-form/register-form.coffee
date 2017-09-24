angular.module('supla-scripts').component 'registerForm',
  templateUrl: 'app/user/register-form/register-form.html'
  controller: (Users, Token, $state) ->
    new class
      $onInit: =>
        @userData = {}

      register: ->
        Users.one('').all('register').post(@userData).then =>
          Token.authenticate(@userData).then ->
            $state.go('dashboard')

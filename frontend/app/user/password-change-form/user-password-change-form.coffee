angular.module('supla-scripts').component 'userPasswordChangeForm',
  templateUrl: 'app/user/password-change-form/user-password-change-form.html'
  bindings:
    user: '<'
  controller: (Token, Notifier) ->
    new class
      changeUserPassword: ->
        @user.withHttpConfig(skipErrorHandler: yes).patch(@change)
        .then =>
          Token.renewToken().then =>
            Notifier.success('Twoje hasło zostało zmienione')
            @change = {}
            @newPasswordConfirmation = ''
        .catch(=> @passwordNotValid = yes)


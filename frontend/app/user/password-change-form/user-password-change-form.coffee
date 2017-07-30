angular.module('supla-scripts').component 'userPasswordChangeForm',
  templateUrl: 'app/user/password-change-form/user-password-change-form.html'
  bindings:
    user: '<'
  controller: (Users, Token, hasRoleFilter, $state, Notifier) ->
    new class
      $onInit: ->
        @isPasswordExpired = Token.isPasswordExpired()
        @user = Token.getCurrentUser() if not @user
        @forCurrentUser = @user.id == Token.getCurrentUser().id

      changeUserPassword: ->
        Users.one(@user.id).withHttpConfig(skipErrorHandler: yes).patch(@change)
        .then =>
          Token.renewToken().then =>
            Notifier.success('Twoje hasło zostało zmienione')
            $state.go(if @forCurrentUser then 'issueList' else 'users.list')
        .catch(=> @passwordNotValid = yes)


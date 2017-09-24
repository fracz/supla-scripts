angular.module('supla-scripts').component 'accountDetails',
  templateUrl: 'app/user/details/account-details.html'
  bindings:
    user: '<'
#  controller: (Users, Token, Notifier, $state) ->
#    new class
#
#      $onInit: ->
#        if not @user
#          @forCurrentUser = yes
#          Users.one(Token.getCurrentUser().id).get().then((@user) =>)
#        else
#          @forCurrentUser = @user.id == Token.getCurrentUser().id
#
#      editUser: ->
#        @editingUser = angular.copy(@user)
#
#      saveEditedUser: ->
#        angular.extend(@user, @editingUser)
#        @user.put().then (@user) =>
#          @editingUser = undefined
#
#      deleteUser: ->
#        @user.remove().then =>
#          if @forCurrentUser
#            Token.forgetRememberedToken()
#            Notifier.info('Twoje konto zostało usunięte')
#          else
#            Notifier.info('Wybrane konto zostało usunięte')
#            $state.go('users.list')
#
#      generateNewPassword: ->
#        @user
#        .withHttpConfig(onError: ['Nie udało wysłać wiadomości e-mail', 'Sprawdź konfigurację aplikacji i spróbuj ponownie'])
#        .patch(generateNewPassword: yes)
#        .then =>
#          @generatingPassword = no
#          Notifier.success('Hasło zostało wygenerowane i przesłane na adres mailowy użytkownika')

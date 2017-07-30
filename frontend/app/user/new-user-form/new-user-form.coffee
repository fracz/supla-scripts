angular.module('supla-scripts').component 'newUserForm',
  templateUrl: 'app/user/new-user-form/new-user-form.html'
  controller: (Users, $state, Notifier) ->
    new class
      $onInit: =>
        @user =
          role: 'coordinator'
          assignedInstanceIds: []

      saveNewUser: ->
        Users.post(@user).then (user) ->
          if not user.activationEmailSent
            Notifier.warning(
              'Nie udało się wysłać wiadomości e-mail z hasłem'
              'Sprawdź konfigurację aplikacji i ponownie wygeneruj hasło dla tego użytkownika'
            )
          $state.go('users.details', user)

angular.module('supla-scripts').component 'accountDelete',
  templateUrl: 'app/user/delete/account-delete.html'
  bindings:
    user: '<'
  controller: (Users, swangular, Token, $state, Notifier, $q) ->
    deleteAccount: ->
      $q.when swal
        type: 'question'
        title: 'Definitywnie?'
        text: 'Podaj swoje hasło w celu potwierdzenia operacji.'
        showCancelButton: yes
        showConfirmButton: yes
        cancelButtonText: 'Anuluj'
        confirmButtonText: 'Tak, usuń moje konto!'
        confirmButtonColor: '#d62c1a'
        showLoaderOnConfirm: true
        input: 'password'
        preConfirm: (password) =>
          @user.withHttpConfig(skipErrorHandler: yes).patch(delete: password).catch(=> $q.reject('Podane hasło jest niepoprawne'))
      .then ->
        Token.forgetRememberedToken()
        Notifier.success('Twoje konto zostało usunięte.')
        $state.go('home')

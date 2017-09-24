angular.module('supla-scripts').component 'accountDelete',
  templateUrl: 'app/user/delete/account-delete.html'
  bindings:
    user: '<'
  controller: ($scope, Users, swangular, Token, $state) ->
    deleteAccount: ->
      swangular.open
        scope: $scope
        type: 'question'
        title: 'Definitywnie?'
        text: 'Nie żartuję, Twoje konto zostanie usunięte!'
        showCancelButton: yes
        showConfirmButton: yes
        cancelButtonText: 'Anuluj'
        confirmButtonText: 'Tak, usuń moje konto!'
        confirmButtonColor: '#d62c1a'
        showLoaderOnConfirm: true
        preConfirm: ->
          Users.one('current').remove().then ->
      .then ->
        Token.forgetRememberedToken()
        $state.go('home')

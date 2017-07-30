angular.module('supla-scripts').component 'userForm',
  templateUrl: 'app/user/new-user-form/user-form.html'
  bindings:
    user: '<'
  controller: (Token) ->
    new class
      $onInit: ->
        @forCurrentUser = @user.id == Token.getCurrentUser().id

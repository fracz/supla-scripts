angular.module('supla-scripts').component 'navbar',
  templateUrl: 'app/common/navbar/navbar.html'
  controller: class
    constructor: ($scope, @Token, @$state) ->
      $scope.$on('AUTH_CHANGED', @syncAuthData)
      @syncAuthData()

    syncAuthData: =>
      @currentUser = @Token.getCurrentUser()

    forgetUser: ->
      @Token.forgetUser().then =>
        @$state.go('home')

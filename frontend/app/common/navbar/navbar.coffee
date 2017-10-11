angular.module('supla-scripts').component 'navbar',
  templateUrl: 'app/common/navbar/navbar.html'
  controller: class
    constructor: ($scope, @Token, @$state, @APP_VERSION) ->
      $scope.$on('AUTH_CHANGED', @syncAuthData)
      @syncAuthData()

    syncAuthData: =>
      @currentUser = @Token.getCurrentUser()

    forgetUser: ->
      @Token.forgetRememberedToken()
      @$state.go('home')

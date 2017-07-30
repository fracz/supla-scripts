angular.module('supla-scripts').component 'homeView',
  controller: ($state, Token, $scope, $timeout) ->
    new class
      $onInit: ->
        if Token.getCurrentUser()
          $timeout(-> $state.go('dashboard'))
        else
          $timeout(-> $state.go('login'))

###
  Idea strongly based on angularjs-viewhead: https://github.com/apparentlymart/angularjs-viewhead
  However, it has problems working with angular-ui-router.
###
angular.module('supla-scripts').directive 'viewTitle', ($rootScope, $timeout) ->
  currentTitle = null

  $rootScope.$on '$stateChangeStart', ->
    $timeout ->
      if not currentTitle # no other view-title has reassigned the title
        delete $rootScope.$viewTitle

  ($scope, $element) ->
    if $element[0].tagName.toLowerCase() is 'view-title'
      $element.remove()

    $scope.$watch (-> $element.html()), ->
      $rootScope.$viewTitle = currentTitle = $element.text().trim()

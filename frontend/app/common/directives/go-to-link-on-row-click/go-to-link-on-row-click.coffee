angular.module('supla-scripts').directive 'goToLinkOnRowClick', ($timeout) ->
  restrict: 'A'
  link: ($scope, $element) ->
    $element.on 'click', (event) ->
      if not angular.element(event.target).is('a')
        link = $($(event.currentTarget).find('a')[0])
        $timeout(-> link.click())

angular.module('supla-scripts').directive 'loadingCover', ->
  restrict: 'A'
  scope: no
  link: (scope, element, attrs) ->
    loadingLayer = angular.element('<div class="loading"></div>')
    element.append(loadingLayer)
    element.addClass('loading-cover')
    scope.$watch attrs.loadingCover, (value) ->
      loadingLayer.toggleClass('ng-hide', !value)
      element.toggleClass('is-loading', !!value)

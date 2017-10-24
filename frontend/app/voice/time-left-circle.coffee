angular.module('supla-scripts').component 'timeLeftCircle',
  template: '<round-progress
    max="$ctrl.time * 2"
    current="$ctrl.timePassed"
    stroke="10"
    radius="20"></round-progress>'
  bindings:
    time: '<'
    onFinished: '&'
  controller: ($scope, ScopeInterval) ->
    new class
      $onInit: ->
        @timePassed = 0
        @cancelHandle = ScopeInterval($scope, @timeDown, 500)

      timeDown: =>
        ++@timePassed
        if ++@timePassed >= @time * 2
          ScopeInterval.cancel(@cancelHandle)
          @onFinished()

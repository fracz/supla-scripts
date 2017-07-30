angular.module('supla-scripts').factory 'ScopeInterval', ($timeout, $q) ->
  nextInterval = (intervalInMs, irregularity) ->
    intervalInMs + (Math.round(Math.random() * irregularity) * (if Math.random() < .5 then 1 else -1))

  ScopeInterval = ($scope, callback, intervalInMs, irregularity = 0) ->
    fn = ->
      $q.when(callback()).finally ->
        cancelHandle.timeoutPromise = $timeout(fn, nextInterval(intervalInMs, irregularity)) if not cancelHandle.cancelled
    cancelHandle =
      timeoutPromise: $timeout(fn, nextInterval(intervalInMs, irregularity))
      unwatchDestroy: $scope.$on('$destroy', -> ScopeInterval.cancel(cancelHandle))

  ScopeInterval.cancel = (cancelHandle) ->
    cancelHandle.unwatchDestroy()
    cancelHandle.cancelled = yes
    $timeout.cancel(cancelHandle.timeoutPromise)

  ScopeInterval

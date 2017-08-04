angular.module('supla-scripts').component 'logsView',
  templateUrl: 'app/logs/logs-view.html'
  controller: (Logs, ScopeInterval, $scope) ->
    new class
      $onInit: ->
        @fetch()
        ScopeInterval($scope, @fetch, 15000, 5000)

      fetch: =>
        Logs.getList().then((@logs) =>)

angular.module('supla-scripts').component 'logsTable',
  templateUrl: 'app/logs/logs-table.html'
  bindings:
    entityId: '<'
    limit: '<'
  controller: (Logs, ScopeInterval, $scope) ->
    new class
      $onInit: ->
        @fetch()
        ScopeInterval($scope, @fetch, 15000, 5000)

      fetch: =>
        Logs.getList({entityId: @entityId, limit: @limit or 100}).then((@logs) =>)

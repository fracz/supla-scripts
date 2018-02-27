angular.module('supla-scripts').component 'logsTable',
  templateUrl: 'app/logs/logs-table.html'
  bindings:
    entityId: '<'
    limit: '<'
    onNewLogs: '&'
  controller: (Logs, ScopeInterval, $scope) ->
    new class
      $onInit: ->
        @page = 0
        @fetch()
        ScopeInterval($scope, @fetch, 20000, 5000)

      fetch: (page = @page) =>
        Logs.getList({entityId: @entityId, limit: @limit or 50, page}).then (@logs) =>
          @page = page
          if @logs.length and @logs[0].id != @newestLogId
            @onNewLogs() if @newestLogId
            @newestLogId = @logs[0].id

angular.module('supla-scripts').component 'stateLogsView',
  templateUrl: 'app/state-logs/state-logs-view.html'
  controller: (Devices, StateLogs) ->
    new class
      $onInit: ->
        Devices.getList().then (@devices) =>
          @channels = {}
          for device in @devices
            @channels[c.id] = c for c in device.channels
          @stateLogs = {}
          @stateGroups = []
          @fetchLogs()

      fetchLogs: (before) =>
        StateLogs.getList().then (stateLogs) =>
          for log in stateLogs
            log.createdAt = moment(log.createdAt).toDate()
            group = @logGroup(log)
            if not @stateLogs[group]
              @stateGroups.push(group)
              @stateGroups.sort()
              @stateLogs[group] = []
            @stateLogs[group].push(log)

      logGroup: (log) ->
        moment(log.createdAt).format('MM.YYYY.MM.DD.HH')


angular.module('supla-scripts').component 'stateLogsView',
  templateUrl: 'app/state-logs/state-logs-view.html'
  controller: (Devices, StateLogs) ->
    new class
      $onInit: ->
        Devices.getList().then (@devices) =>
          @channels = {}
          for device in @devices
            @channels[c.id] = c for c in device.channels
          @stateLogs = []
          @fetchLogs()

      more: =>
        @fetchLogs(moment(@stateLogs[@stateLogs.length - 1].createdAt).format())

      fetchLogs: (before) =>
        StateLogs.getList({before, limit: 50}).then (stateLogs) =>
          for log in stateLogs
            log.createdAt = moment(log.createdAt).toDate()
            log.channelLog = angular.copy(@channels[log.channelId])
            log.channelLog.state = log.state
            @stateLogs.push(log)


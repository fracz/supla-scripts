angular.module('supla-scripts').component 'stateLogsView',
  templateUrl: 'app/state-logs/state-logs-view.html'
  controller: (Devices, StateLogs, $stateParams, $state) ->
    new class
      $onInit: ->
        @channelId = +$stateParams.channelId or undefined
        Devices.getList().then (@devices) =>
          @channels = {}
          for device in @devices
            @channels[c.id] = c for c in device.channels
          @stateLogs = []
          @fetchLogs()
        @LOGGABLE_FUNCTIONS = [
          'POWERSWITCH',
          'LIGHTSWITCH',
        ]

      more: =>
        @fetchLogs(moment(@stateLogs[@stateLogs.length - 1].createdAt).format())

      fetchLogs: (before) =>
        StateLogs.getList({before, limit: 50, channelId: @channelId}).then (stateLogs) =>
          @showMore = stateLogs.length > 0
          for log in stateLogs
            log.createdAt = moment(log.createdAt).toDate()
            log.channelLog = angular.copy(@channels[log.channelId])
            log.channelLog.state = log.state
            @stateLogs.push(log)

      showLogsForChannel: =>
        $state.go('stateLogs', {channelId: @channelId}, {reload: true})




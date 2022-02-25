angular.module('supla-scripts').component 'stateLogsView',
  templateUrl: 'app/state-logs/state-logs-view.html'
  controller: (Devices, StateLogs, $stateParams, $state, $scope) ->
    new class
      $onInit: ->
        @channelId = +$stateParams.channelId or undefined
        Devices.getList().then (@devices) =>
          @channels = {}
          for device in @devices
            @channels[c.id] = c for c in device.channels
          @loadNewestLogs()
          $scope.$on('refreshLogs', @loadNewestLogs)

        @LOGGABLE_FUNCTIONS = [
          'POWERSWITCH',
          'LIGHTSWITCH',
          'THERMOMETER',
          'OPENINGSENSOR_DOOR',
          'OPENINGSENSOR_GATEWAY',
          'HUMIDITY',
          'HUMIDITYANDTEMPERATURE',
          'OPENINGSENSOR_GATE',
          'OPENINGSENSOR_GARAGEDOOR',
          'OPENINGSENSOR_WINDOW',
          'OPENINGSENSOR_ROOFWINDOW',
          'MAILSENSOR',
          'ELECTRICITYMETER',
          'IC_ELECTRICITYMETER',
          'GASMETER',
          'IC_GASMETER',
          'WATERMETER',
          'IC_WATERMETER',
          'ACTION_TRIGGER',
        ]

      loadNewestLogs: =>
        @stateLogs = []
        @fetchLogs()

      more: =>
        @fetchLogs(moment(@stateLogs[@stateLogs.length - 1].createdAt).unix())

      fetchLogs: (before) =>
        @loading = true
        StateLogs.getList({before, limit: 50, channelId: @channelId}).then (stateLogs) =>
          @showMore = stateLogs.length > 0
          for log in stateLogs
            log.createdAt = moment(log.createdAt).toDate()
            log.channelLog = angular.copy(@channels[log.channelId])
            if log.channelLog
              log.channelLog.state = log.state
              @stateLogs.push(log)
        .finally(() => @loading = false)

      showLogsForChannel: =>
        $state.go('stateLogs', {channelId: @channelId}, {reload: true})




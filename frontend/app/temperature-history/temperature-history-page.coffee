angular.module('supla-scripts').component 'temperatureHistoryPage',
  templateUrl: 'app/temperature-history/temperature-history-page.html'
  controller: ($scope, Channels, channelLabelFilter, swangular) ->
    new class
      $onInit: ->
        Channels.getList(['FNC_THERMOMETER', 'FNC_HUMIDITYANDTEMPERATURE']).then((@sensors) =>)
        @dateRange =
          startDate: moment().subtract(24, 'hours').startOf('minute')
          endDate: moment().endOf('minute')
        @onDateRangeChange()

      downloadDataForSensor: (sensor) ->
        if not sensor.status
          sensor.status = 'downloading'
          Channels.getLogs(sensor.id, @dateRange)
          .finally(-> sensor.status = 'downloaded')
          .then (logs) =>
            if not logs?.length
              sensor.status = 'error'
              swangular.open
                scope: $scope
                type: 'error'
                title: 'Brak danych'
                text: 'Serwer nie zwrócił żadnych danych dla wybranego termometru i okresu.'
              return
            @timestamps = @timestamps.concat(logs.map((log) -> parseInt(log.date_timestamp))).filter((t, i, s) -> s.indexOf(t) is i).sort()
            hasHumidity = logs[0].humidity != undefined
            hasTemperature = logs[0].temperature != undefined
            if hasTemperature
              row =
                label: channelLabelFilter(sensor) + (if hasHumidity then ' (temperatura)' else '')
                yAxisID: 'temperature'
                data: {}
              for log in logs
                timestamp = parseInt(log.date_timestamp)
                row.data[timestamp] = parseFloat(log.temperature)
              @data.push(row)
            if hasHumidity
              row =
                label: channelLabelFilter(sensor) + (if hasTemperature then ' (wilgotność)' else '')
                yAxisID: 'humidity'
                borderDash: [10, 5]
                data: {}
              for log in logs
                timestamp = parseInt(log.date_timestamp)
                row.data[timestamp] = parseFloat(log.humidity)
              @data.push(row)

            $scope.labels = @timestamps.map(moment.unix)

            $scope.data = []
            $scope.datasetOverride = []

            for row in @data
              $scope.data.push(row.data[timestamp] for timestamp in @timestamps)
              $scope.datasetOverride.push
                label: row.label
                yAxisID: row.yAxisID
                borderDash: row.borderDash
                borderWidth: 4
                fill: no


      onDateRangeChange: ->
        delete sensor.status for sensor in @sensors if @sensors
        @timestamps = []
        @data = []
        $scope.data = []
        $scope.datasetOverride = []

      chartOptions:
        legend: {display: true, position: 'top'}
        elements: { point: { radius: 0, hitRadius: 10, hoverRadius: 10 } }
        scales:
          xAxes: [
            {
              type: 'time'
              time:
                displayFormats:
                  'minute': 'LT'
                  'hour': 'lll'
            }
          ]
          yAxes: [
            {
              id: 'temperature'
              type: 'linear'
              display: true
              position: 'left'
              scaleLabel:
                display: true,
                labelString: 'Temperatura °C'
            }
            {
              id: 'humidity'
              type: 'linear'
              display: true
              position: 'right'
              scaleLabel:
                display: true
                labelString: 'Wilgotność %'
              ticks:
                max: 100
                min: 0
            }
          ]


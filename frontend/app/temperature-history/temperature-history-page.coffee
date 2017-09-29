angular.module('supla-scripts').component 'temperatureHistoryPage',
  templateUrl: 'app/temperature-history/temperature-history-page.html'
  controller: ($scope, Channels) ->
#    $scope.labels = ["January", "February", "March", "April", "May", "June", "July"];
#    $scope.series = ['KANAŁ', 'Series B'];
    $scope.data = []
    $scope.datasetOverride = []
    #      [65, 59, 80, 81, 56, 55, 40],
    #      [28, 48, 40, 19, 86, 27, 90]
    #    ];
    $scope.onClick = (points, evt) ->
      console.log(points, evt);
    #    $scope.datasetOverride = [{yAxisID: 'y-axis-1'}, {yAxisID: 'y-axis-2'}];
    $scope.options = {
      legend: {display: true, position: 'top'}
      elements: { point: { radius: 0, hitRadius: 10, hoverRadius: 10 } }
      scales: {
        xAxes: [
          {
            type: 'time',
            time:
              displayFormats: {
#                'millisecond': 'MMM DD',
#                'second': 'MMM DD',
                'minute': 'LT',
                'hour': 'lll',
#                'day': 'MMM DD',
#                'week': 'MMM DD',
#                'month': 'MMM DD',
#                'quarter': 'MMM DD',
#                'year': 'MMM DD',
              }
          }
        ]
        yAxes: [
          {
            id: 'temperature',
            type: 'linear',
            display: true,
            position: 'left'
            scaleLabel: {
              display: true,
              labelString: 'Temperatura °C'
            }
          }
          {
            id: 'humidity',
            type: 'linear',
            display: true,
            position: 'right'
            scaleLabel: {
              display: true,
              labelString: 'Wilgotność %'
            }
            ticks: {
              max: 100,
              min: 0
            }
          }
        ]
      }
    };


    new class
      $onInit: ->
        @period = '-1hour'
        Channels.getList(['FNC_THERMOMETER', 'FNC_HUMIDITYANDTEMPERATURE']).then((@sensors) =>)

      downloadDataForSensor: (sensor) ->
        if not sensor.status
          sensor.status = 'downloading'
          Channels.getLogs(sensor.id, @period).then (logs) =>
            sensor.status = 'downloaded'
            $scope.labels = logs.map((log) -> moment.unix(log.date_timestamp).toDate())
            hasHumidity = logs[0].humidity != undefined
            $scope.data.push(logs.map((log) -> parseFloat(log.temperature)))
            $scope.datasetOverride.push
              label: sensor.caption + (if hasHumidity then ' (temperatura)' else ''),
              yAxisID: 'temperature',
              borderWidth: 5
              fill: no
            if hasHumidity
              $scope.data.push(logs.map((log) -> parseFloat(log.humidity)))
              $scope.datasetOverride.push
                label: sensor.caption + ' (wilgotność)',
                yAxisID: 'humidity',
                fill: no

      changePeriod: (@period) ->
        delete sensor.status for sensor in @sensors
        $scope.data = []
        $scope.datasetOverride = []


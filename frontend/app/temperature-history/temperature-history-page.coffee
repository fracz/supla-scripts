angular.module('supla-scripts').component 'temperatureHistoryPage',
  templateUrl: 'app/temperature-history/temperature-history-page.html'
  controller: ($scope, Channels) ->
    $scope.labels = ["January", "February", "March", "April", "May", "June", "July"];
    $scope.series = ['KANAŁ', 'Series B'];
    $scope.data = [
      [65, 59, 80, 81, 56, 55, 40],
      [28, 48, 40, 19, 86, 27, 90]
    ];
    $scope.onClick = (points, evt) ->
      console.log(points, evt);
#    $scope.datasetOverride = [{yAxisID: 'y-axis-1'}, {yAxisID: 'y-axis-2'}];
    $scope.options = {
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
            id: 'y-axis-1',
            type: 'linear',
            display: true,
            position: 'left'
          }
#          {
#            id: 'y-axis-2',
#            type: 'linear',
#            display: true,
#            position: 'right'
#          }
        ]
      }
    };


    new class
      $onInit: ->
        @period = '-1hour'
        Channels.getList(['FNC_THERMOMETER', 'FNC_HUMIDITYANDTEMPERATURE']).then((@sensors) => console.log(@sensors))

      toggleSensor: (sensor) ->
        if not @toggling
          @toggling = yes
          sensor.active = !sensor.active
          if sensor.active
            Channels.getLogs(sensor.id, @period).then (logs) =>
              $scope.labels = logs.map((log) -> moment.unix(log.date_timestamp).toDate())
              $scope.series = @sensors.filter((s) -> s.active).map((s) -> s.caption)
              $scope.data = [logs.map((log) -> log.temperature)]
            .finally(=> @toggling = no)

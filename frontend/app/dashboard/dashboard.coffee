angular.module('supla-scripts').component 'dashboard',
  templateUrl: 'app/dashboard/dashboard.html'
  controller: class
    constructor: (Devices) ->
      Devices.getList().then((@devices) =>)

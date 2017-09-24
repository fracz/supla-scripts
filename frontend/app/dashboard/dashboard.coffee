angular.module('supla-scripts').component 'dashboard',
  templateUrl: 'app/dashboard/dashboard.html'
  controller: class
    constructor: (Devices, Restangular) ->
      Restangular.one('info').get()
      Devices.getList().then((@devices) =>)

angular.module('supla-scripts').component 'dashboard',
  templateUrl: 'app/dashboard/dashboard.html'
  controller: (Devices, Channels, Scenes, ScopeInterval, $scope) ->
    new class
      $onInit: ->
        ScopeInterval($scope, @fetchDevices, 7000, 2000)
        @fetchDevices()
        Scenes.getList().then((@scenes) =>)

      fetchDevices: =>
        Devices.getList().then((@devices) =>)

      executeChannelAction: (channel, action) ->
        Channels.executeAction(channel.id, {action}).then (newState) ->
          angular.extend(channel.state, newState) if angular.isObject(newState)

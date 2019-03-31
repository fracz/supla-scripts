angular.module('supla-scripts').component 'dashboard',
  templateUrl: 'app/dashboard/dashboard.html'
  controller: (Devices, Channels, Scenes, $timeout, ScopeInterval, $scope) ->
    new class
      $onInit: ->
#        ScopeInterval($scope, @fetchDevices, 7000, 2000)
        @fetchDevices()
        Scenes.getList().then((@scenes) =>)

      fetchDevices: =>
        Devices.getList().then((@devices) =>)

      executeChannelAction: (channel, action) ->
        Channels.executeAction(channel.id, {action}).then (newState) =>
          newState = newState.plain()
          if !angular.equals(channel.state, newState) or action == 'getChannelState'
            angular.extend(channel.state, newState) if angular.isObject(newState)
          else
            $timeout((=> @executeChannelAction(channel, 'getChannelState')), 1500)

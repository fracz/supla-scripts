angular.module('supla-scripts').component 'dashboard',
  templateUrl: 'app/dashboard/dashboard.html'
  controller: (Devices, Channels, Scenes) ->
    new class
      $onInit: ->
        Devices.getList().then((@devices) =>)
        Scenes.getList().then((@scenes) =>)

      executeChannelAction: (channel, action) ->
        Channels.executeAction(channel.id, {action}).then (channelWithState) ->
          angular.extend(channel, channelWithState) if angular.isObject(channelWithState)

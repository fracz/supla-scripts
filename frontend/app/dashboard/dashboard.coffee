angular.module('supla-scripts').component 'dashboard',
  templateUrl: 'app/dashboard/dashboard.html'
  controller: (Devices, Channels) ->
    new class
      $onInit: ->
        Devices.getList().then((@devices) =>)

      executeChannelAction: (channel, action) ->
        Channels.executeAction(channel.id, {action}).then (channelWithState) ->
          angular.extend(channel, channelWithState) if angular.isObject(channelWithState)

angular.module('supla-scripts').service 'Channels', (Restangular, Devices) ->

  Channels = Restangular.service('channels')

  addChannelMethods = (channel) ->
    channel.getState = ->
      Channels.one(channel.id).get().then (channelWithState) ->
        angular.extend(channel, channelWithState)

  service =
    getList: (functions = []) ->
      Devices.getList().then (devices) =>
        channels = []
        for device in devices
          for channel in device.channels when (!functions?.length or channel.function.name in functions)
            channel.device = device
            addChannelMethods(channel)
            channels.push(channel)
        channels

    get: (channelId) ->
      service.getList().then (channels) ->
        channels.filter((channel) -> channel.id is channelId)[0]

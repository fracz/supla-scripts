angular.module('supla-scripts').service 'Channels', (Restangular, Devices, CacheFactory) ->

  Channels = Restangular.service('channels')

  channelsCache = CacheFactory.get('channelsCache') or CacheFactory 'channelsCache',
    maxAge: 30 * 1000
    deleteOnExpire: 'aggressive'

  addChannelMethods = (channel) ->
    channel.getState = ->
      Channels.one(channel.id).withHttpConfig(cache: channelsCache).get().then (channelWithState) ->
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

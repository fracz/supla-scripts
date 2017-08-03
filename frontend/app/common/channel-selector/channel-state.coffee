angular.module('supla-scripts').component 'channelState',
  bindings:
    channelId: '<'
    channel: '<'
  templateUrl: 'app/common/channel-selector/channel-state.html'
  controller: class
    constructor: (@Channels) ->

    $onChanges: =>
      if not @channel
        @Channels.get(@channelId).then (@channel) =>
          @channel.getState()

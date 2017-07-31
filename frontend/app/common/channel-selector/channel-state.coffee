angular.module('supla-scripts').component 'channelState',
  bindings:
    channelId: '<'
  templateUrl: 'app/common/channel-selector/channel-state.html'
  controller: class
    constructor: (@Channels) ->

    $onChanges: =>
      @Channels.get(@channelId).then (@channel) =>
        @channel.getState()

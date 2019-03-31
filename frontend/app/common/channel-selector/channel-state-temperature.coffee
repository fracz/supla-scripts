angular.module('supla-scripts').component 'channelStateTemperature',
  bindings:
    channelId: '<'
  template: '<span ng-hide="!$ctrl.channel.state.temperature">{{ $ctrl.channel.state.temperature | number:2 }}&deg;C</span>'
  controller: class
    constructor: (@Channels) ->

    $onChanges: =>
      @Channels.get(@channelId).then (@channel) =>
        @channel.getState()

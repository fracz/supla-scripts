angular.module('supla-scripts').component 'channelStateHumidity',
  bindings:
    channelId: '<'
  template: '<span ng-hide="!$ctrl.channel.humidity">{{ $ctrl.channel.humidity | number:1 }}%</span>'
  controller: class
    constructor: (@Channels) ->

    $onChanges: =>
      @Channels.get(@channelId).then (@channel) =>
        @channel.getState()

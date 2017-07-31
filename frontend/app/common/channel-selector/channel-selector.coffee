angular.module('supla-scripts').component 'channelSelector',
  templateUrl: 'app/common/channel-selector/channel-selector.html'
  bindings:
    functions: '<'
    hideIds: '<'
  require:
    ngModel: 'ngModel'
  controller: class
    constructor: (@Channels) ->

    $onInit: =>
      @Channels.getList(@functions).then (@channels) =>
        @ngModel.$render = => @chosenChannelId = @ngModel.$viewValue

    isNotHidden: (channel) =>
      channel.id not in (@hideIds or [])

    updateModel: ->
      @ngModel.$setViewValue(@chosenChannelId)

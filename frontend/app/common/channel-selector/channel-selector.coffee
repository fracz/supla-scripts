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

    updateModel: ->
      @ngModel.$setViewValue(@chosenChannelId)

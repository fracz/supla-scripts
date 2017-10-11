angular.module('supla-scripts').component 'channelActionButtonSelector',
  template: '<button type="button" class="btn btn-success btn-lg" ng-click="$ctrl.nextOperation()" ng-disabled="$ctrl.disabled">
                {{ $ctrl.currentOperation.label.toUpperCase() }}</button>'
  bindings:
    channel: '<'
    disabled: '<'
  controller: (CHANNEL_AVAILABLE_ACTIONS) ->
    new class
      $onChanges: ->
        @availableOperations = CHANNEL_AVAILABLE_ACTIONS[@channel.function.name]
        @index = 0
        @nextOperation()

      nextOperation: ->
        @currentOperation = @availableOperations[@index++]
        @index = 0 if @index >= @availableOperations.length
``

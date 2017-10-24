angular.module('supla-scripts').component 'channelActionButtonSelector',
  template: '<button type="button" class="btn btn-success btn-lg" ng-click="$ctrl.nextOperation()" ng-disabled="$ctrl.disabled">
                {{ $ctrl.currentOperation.label.toUpperCase() }}</button>'
  bindings:
    channel: '<'
    disabled: '<'
  require:
    ngModel: 'ngModel'
  controller: (CHANNEL_AVAILABLE_ACTIONS) ->
    new class
      $onInit: ->
        @ngModel.$render = => @$onChanges()

      $onChanges: (changes) =>
        @availableOperations = CHANNEL_AVAILABLE_ACTIONS[@channel.function.name]
        @index = 0
        if @ngModel.$viewValue
          @index = @availableOperations.map((o) -> o.action).indexOf(@ngModel.$viewValue)
          @index = 0 if @index <= 0
        @nextOperation(changes?.channel?.isFirstChange())

      nextOperation: (isFirstChange) ->
        @currentOperation = @availableOperations[@index++]
        @index = 0 if @index >= @availableOperations.length
        @ngModel.$setViewValue(@currentOperation.action) if not isFirstChange

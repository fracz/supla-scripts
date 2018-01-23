angular.module('supla-scripts').component 'timeSceneSelector',
  templateUrl: 'app/scenes/selector/time-scene-selector.html'
  bindings:
    disabled: '<'
  require:
    ngModel: 'ngModel'
  controller: ->
    new class
      $onInit: ->
        @ngModel.$render = =>
          value = @ngModel.$viewValue or {}
          @offsets = Object.keys(value).map((offset) -> parseInt(offset)).sort((a, b) -> a - b)
          @offsets.push(0) if not @offsets.length
          @actions = (value[offset] for offset in @offsets)

      deleteDelayedActions: (index) ->
        @offsets.splice(index, 1)
        @actions.splice(index, 1)
        @onChange()

      onChange: ->
        if not @disabled
          value = {}
          for offset, index in @offsets
            if value[offset]
              value[offset] += "|" + @actions[index] if @actions[index]
            else
              value[offset] = @actions[index]
          @ngModel.$setViewValue(value)

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
          @offsets = Object.keys(value).sort()
          @offsets.push(0) if not @offsets.length
          @actions = (value[offset] for offset of @offsets)

      onChange: ->
        if not @disabled
          value = {}
          for offset, index in @offsets
            if value[offset]
              value[offset] += "|" + @actions[index]
            else
              value[offset] = @actions[index]
          @ngModel.$setViewValue(value)

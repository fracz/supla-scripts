angular.module('supla-scripts').component 'voiceTriggersField',
  templateUrl: 'app/voice/voice-triggers-field.html'
  require:
    ngModel: 'ngModel'
  controller: ->
    new class
      $onInit: ->
        @ngModel.$render = =>
          @triggers = (@ngModel.$viewValue or []).map((text) -> {text})

      onChange: ->
        triggers = @triggers
          .map((trigger) -> trigger.text)
          .filter((text) -> text.trim())
        @ngModel.$setViewValue(triggers)

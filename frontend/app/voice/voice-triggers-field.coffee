angular.module('supla-scripts').component 'voiceTriggersField',
  templateUrl: 'app/voice/voice-triggers-field.html'
#  bindings:
#    voiceCommand: '<'
#    onSubmit: '&'
#    onCancel: '&'
  require:
    ngModel: 'ngModel'
  controller: ->
    new class
      $onInit: ->
        if @voiceCommand
          @voiceCommand = angular.copy(@voiceCommand.plain?() or @voiceCommand)
        else
          @voiceCommand = {}
        @voiceCommand.triggers ?= []
        @voiceCommand.triggers = @voiceCommand.triggers.map((text) -> {text})

      submit: ->
        savedCommand = angular.copy(@voiceCommand)
        savedCommand.triggers = savedCommand.triggers.map((trigger) -> trigger.text).filter((text) -> !!text)
        @onSubmit({savedCommand})


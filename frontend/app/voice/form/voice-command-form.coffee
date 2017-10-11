angular.module('supla-scripts').component 'voiceCommandForm',
  templateUrl: 'app/voice/form/voice-command-form.html'
  bindings:
    voiceCommand: '<'
    onSubmit: '&'
    onCancel: '&'
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


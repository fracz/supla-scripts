angular.module('supla-scripts').component 'voiceCommandForm',
  templateUrl: 'app/voice/form/voice-command-form.html'
  bindings:
    onSubmit: '&'
    onCancel: '&'
  controller: (VoiceCommands) ->
    new class
      voiceCommand:
        triggers: []



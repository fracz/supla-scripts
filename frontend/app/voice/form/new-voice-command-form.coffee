angular.module('supla-scripts').component 'newVoiceCommandForm',
  templateUrl: 'app/voice/form/new-voice-command-form.html'
  bindings:
    showTutorial: '<'
    onSubmit: '&'
  controller: ->
    new class
      commandAdded: (command) ->
        @newCommand =
          triggers: [command]

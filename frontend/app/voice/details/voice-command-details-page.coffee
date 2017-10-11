angular.module('supla-scripts').component 'voiceCommandDetailsPage',
  templateUrl: 'app/voice/details/voice-command-details-page.html'
  bindings:
    voiceCommand: '<'
  controller: (VoiceCommands) ->
    new class
      saveVoiceCommand: (newData) ->
        debugger
        angular.extend(@voiceCommand, newData)
        @voiceCommand.put().then =>
          @editing = false

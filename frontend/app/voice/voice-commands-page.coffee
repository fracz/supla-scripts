angular.module('supla-scripts').component 'voiceCommandsPage',
  templateUrl: 'app/voice/voice-commands-page.html'
  controller: (VoiceCommands, swangular, $scope) ->
    $onInit: ->
      VoiceCommands.getList().then((@voiceCommands) =>)

    addNewVoiceCommand: (voiceCommand) ->
      VoiceCommands.post(voiceCommand).then (savedCommand) =>
        @voiceCommands.push(savedCommand)
        @adding = false





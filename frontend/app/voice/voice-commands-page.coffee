angular.module('supla-scripts').component 'voiceCommandsPage',
  templateUrl: 'app/voice/voice-commands-page.html'
  controller: (VoiceCommands, $state) ->
    $onInit: ->
      VoiceCommands.getList().then((@voiceCommands) =>)

    addNewVoiceCommand: (voiceCommand) ->
      VoiceCommands.post(voiceCommand).then (savedCommand) =>
        @voiceCommands.push(savedCommand)
        $state.go('voice.details', {id: savedCommand.id})

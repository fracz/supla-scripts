angular.module('supla-scripts').component 'voiceCommandsPage',
  templateUrl: 'app/voice/voice-commands-page.html'
  controller: (VoiceCommands, swangular, $scope) ->
    $onInit: ->
      VoiceCommands.getList().then((@voiceCommands) =>)

    addNewVoiceCommand: (voiceCommand) ->
      VoiceCommands.post(voiceCommand).then (savedCommand) =>
        @voiceCommands.push(savedCommand)
        @adding = false

    saveVoiceCommand: (voiceCommand, newData) ->
      angular.extend(voiceCommand, newData)
      voiceCommand.put().then ->
        voiceCommand.editing = false

    deleteVoiceCommand: (voiceCommand) ->
      swangular.open
        scope: $scope
        type: 'question'
        title: 'Na pewno?'
        text: 'Czy chcesz usunąć tą komendę głosową?!'
        showCancelButton: yes
        showConfirmButton: yes
        cancelButtonText: 'Anuluj'
        confirmButtonText: 'Tak, usuń'
        confirmButtonColor: '#d62c1a'
        showLoaderOnConfirm: true
        preConfirm: => voiceCommand.remove()
      .then =>
        @voiceCommands.splice(@voiceCommands.indexOf(voiceCommand), 1)

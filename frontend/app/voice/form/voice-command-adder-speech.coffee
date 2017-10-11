angular.module('supla-scripts').component 'voiceCommandAdderSpeech',
  templateUrl: 'app/voice/form/voice-command-adder-speech.html'
  bindings:
    secondsLimit: '<'
    onAdded: '&'
    onCancelled: '&'
  controller: (VoiceCommands, ScopeInterval, $scope) ->
    new class
      $onInit: ->
        @secondsLimit ?= 45
        VoiceCommands.one('last').get().then ({command}) =>
          @lastCommand = command
          @waitingForVoice = true
          @cancel = ScopeInterval($scope, @checkForNewCommand, 3000)

      checkForNewCommand: (forceCancel) =>
        VoiceCommands.one('last').get().then ({command}) =>
          if command != @lastCommand
            ScopeInterval.cancel(@cancel)
            @onAdded({command})
          else if forceCancel
            ScopeInterval.cancel(@cancel)
            @onCancelled()

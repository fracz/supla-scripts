angular.module('supla-scripts').component 'voiceCommandDetailsPage',
  templateUrl: 'app/voice/details/voice-command-details-page.html'
  bindings:
    voiceCommand: '<'
  controller: (swangular, $scope, $state, Notifier) ->
    new class
      saveVoiceCommand: (newData) ->
        angular.extend(@voiceCommand, newData)
        @voiceCommand.put().then =>
          $state.go($state.current.name, {}, reload: yes)

      deleteVoiceCommand: ->
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
          preConfirm: => @voiceCommand.remove()
        .then =>
          Notifier.success('Komenda została usunięta.')
          $state.go('voice', {}, reload: yes)

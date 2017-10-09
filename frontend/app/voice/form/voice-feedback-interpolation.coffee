angular.module('supla-scripts').component 'voiceFeedbackInterpolation',
  templateUrl: 'app/voice/form/voice-feedback-interpolation.html'
  bindings:
    feedback: '<'
  controller: (VoiceCommands) ->
    new class
      fetching: no
      pending: no

      $onChanges: ->
        if not @fetching
          if @feedback
            @interpolatedFeedback ?= @feedback
            @pending = no
            @fetching = yes
            VoiceCommands.one('feedback').patch(feedback: @feedback)
              .then((@interpolatedFeedback) =>)
              .finally =>
                @fetching = no
                @$onChanges() if @pending
        else
          @pending = yes

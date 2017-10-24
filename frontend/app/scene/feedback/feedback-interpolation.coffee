angular.module('supla-scripts').component 'feedbackInterpolation',
  templateUrl: 'app/scenes/feedback/feedback-interpolation.html'
  bindings:
    feedback: '<'
  controller: (Scenes) ->
    new class
      fetching: no
      pending: no

      $onChanges: ->
        if not @fetching
          if @feedback
            @interpolatedFeedback ?= @feedback
            @pending = no
            @fetching = yes
            Scenes.one('feedback').patch(feedback: @feedback)
              .then((@interpolatedFeedback) =>)
              .finally =>
                @fetching = no
                @$onChanges() if @pending
        else
          @pending = yes

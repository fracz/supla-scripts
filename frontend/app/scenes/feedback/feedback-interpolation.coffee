angular.module('supla-scripts').component 'feedbackInterpolation',
  templateUrl: 'app/scenes/feedback/feedback-interpolation.html'
  bindings:
    feedback: '<'
    condition: '<'
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
              .then((feedback) => @interpolatedFeedback = feedback?.plain?() or feedback or '')
              .finally =>
                @fetching = no
                @$onChanges() if @pending
                @conditionMet = (not @feedback) or (@interpolatedFeedback and @interpolatedFeedback != '0')
        else
          @pending = yes

angular.module('supla-scripts').component 'feedbackInterpolation',
  templateUrl: 'app/scenes/feedback/feedback-interpolation.html'
  bindings:
    feedback: '<'
    condition: '<'
    refreshing: '<'
  controller: (Scenes, $scope, ScopeInterval) ->
    new class
      fetching: no
      pending: no

      $onInit: ->
        if @refreshing
          ScopeInterval($scope, (() => @$onChanges()), 10000, 1000)

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

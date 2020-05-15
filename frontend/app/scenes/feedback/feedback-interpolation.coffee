angular.module('supla-scripts').component 'feedbackInterpolation',
  templateUrl: 'app/scenes/feedback/feedback-interpolation.html'
  bindings:
    feedback: '<'
    condition: '<'
    refreshing: '<'
    displayUsedChannels: '<'
  controller: (Scenes, $scope, ScopeInterval, $timeout) ->
    new class
      fetching: no
      pending: no
      debouncing: no

      $onInit: ->
        if @refreshing
          ScopeInterval($scope, (() => @$onChanges()), 10000, 1000)

      $onChanges: ->
        if not @fetching and not @debouncing
          if @feedback
            @interpolatedFeedback ?= @feedback
            @pending = no
            @fetching = yes
            Scenes.one('feedback').patch(feedback: @feedback)
              .then (feedback) =>
                @interpolatedFeedback = feedback?.feedback or ''
                @usedChannelsIds = feedback?.usedChannelsIds or []
              .then(() => @error = @interpolatedFeedback.indexOf('ERROR: ') > 0)
              .finally =>
                @fetching = no
                @conditionMet = (not @feedback) or (@interpolatedFeedback and @interpolatedFeedback != '0')
                @debouncing = yes
                $timeout =>
                  @debouncing = no
                  @$onChanges() if @pending
                , 500
        else
          @pending = yes

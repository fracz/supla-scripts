angular.module('supla-scripts').component 'sceneAtTriggers',
  templateUrl: 'app/scenes/at-triggers/scene-at-triggers.html'
  bindings:
    disabled: '<'
  require:
    ngModel: 'ngModel'
  controller: (Channels) ->
    new class
      $onInit: ->
        Channels.getList(['ACTION_TRIGGER']).then (@channels) =>
          @channels[c.id] = c for c in @channels
        @ngModel.$render = => @actionTriggers = @ngModel.$viewValue || []

      addNewSceneAt: (channelId) ->
        @actionTriggers.push({channelId, trigger: ''})
        @newChannel = undefined
        @updateModel()

      deleteSceneAt: (trigger) ->
        @actionTriggers.splice(@actionTriggers.indexOf(trigger), 1)
        @updateModel()

      updateModel: ->
        @ngModel.$setViewValue(@actionTriggers)

angular.module('supla-scripts').component 'sceneSelector',
  templateUrl: 'app/scene/scene-selector.html'
  bindings:
    disabled: '<'
  require:
    ngModel: 'ngModel'
  controller: (Channels, $scope, $q) ->
    new class
      scene: []

      $onInit: ->
        @ngModel.$render = =>
          sceneStrings = (@ngModel.$viewValue or '').split('|').filter((e) -> !!e)
          promises = sceneStrings.map((sceneString) -> Channels.get(sceneString.split(',')[0]))
          $q.all(promises).then (channels) =>
            @scene = sceneStrings.map (sceneString, index) =>
              channel: channels[index]
              action: sceneString.split(',')[1]
        $scope.$watch('$ctrl.scene.length', => @usedChannelIds = @scene.map((o) -> o.channel.id))

      addNewChannelToScene: (newChannelId) ->
        if (newChannelId)
          Channels.get(newChannelId).then (channel) =>
            @scene.push({channel})

      chooseAction: (operation, action) ->
        operation.action = action
        @onChange()

      onChange: ->
        operationsWithActions = @scene.filter((operation) -> !!operation.action)
        sceneString = operationsWithActions.map((operation) -> "#{operation.channel.id},#{operation.action}").join('|')
        @ngModel.$setViewValue(sceneString)

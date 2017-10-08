angular.module('supla-scripts').component 'sceneSelector',
  templateUrl: 'app/scene/scene-selector.html'
  require:
    ngModel: 'ngModel'
  controller: (Channels, $scope) ->
    new class
      scene: []

      $onInit: ->
        @ngModel.$render = => @scene = (@ngModel.$viewValue or '').split('|').filter((e) -> !!e)
        $scope.$watch('$ctrl.scene.length', => @usedChannelIds = @scene.map((o) -> o.channel.id))

      onChange: ->
        sceneString = @scene.join('|')
        @ngModel.$setViewValue(sceneString)

      addNewChannelToScene: (newChannelId) ->
        if (newChannelId)
          Channels.get(newChannelId).then (channel) =>
            @scene.push({channel})

      chooseAction: (operation, action) ->
        operation.action = action

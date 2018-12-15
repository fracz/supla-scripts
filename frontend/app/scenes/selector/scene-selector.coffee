angular.module('supla-scripts').component 'sceneSelector',
  templateUrl: 'app/scenes/selector/scene-selector.html'
  bindings:
    disabled: '<'
  require:
    ngModel: 'ngModel'
  controller: (Channels, Thermostats, Scenes, $scope, $q, CHANNEL_AVAILABLE_ACTIONS) ->
    new class
      scene: []

      $onInit: ->
        @sequence = Math.round(Math.random() * 100000)
        @sceneableFunctions = Object.keys(CHANNEL_AVAILABLE_ACTIONS)
        @ngModel.$render = =>
          sceneStrings = (@ngModel.$viewValue or '').split('|').filter((e) -> !!e)
          promises = sceneStrings.map (sceneString) ->
            parts = sceneString.split(';')
            if parts[1].indexOf('thermostat') == 0
              Thermostats.get(parts[0], simple: yes)
            else if parts[1].indexOf('scene') == 0
              Scenes.get(parts[0])
            else
              Channels.get(parts[0])
          @loadingChannels = yes
          $q.all(promises).then (entities) =>
            @loadingChannels = no
            @scene = sceneStrings.map (sceneString, index) =>
              parts = sceneString.split(';')
              model = {action: parts[1]}
              if parts[1].indexOf('thermostat') == 0
                model.thermostat = entities[index]
              else if parts[1].indexOf('scene') == 0
                model.scene = entities[index]
              else
                model.channel = entities[index]
              model
        $scope.$watch '$ctrl.scene.length', =>
          @usedChannelIds = @scene.filter((o) -> o.channel).map((o) -> o.channel.id)
          @usedThermostatIds = @scene.filter((o) -> o.thermostat).map((o) -> o.thermostat.id)
          @usedSceneIds = @scene.filter((o) -> o.scene).map((o) -> o.scene.id)
          @onChange()

      addNewChannelToScene: (newChannelId) ->
        if (newChannelId)
          Channels.get(newChannelId).then (channel) =>
            @scene.push({channel})

      addNewThermostatToScene: (thermostat) ->
        @scene.push({thermostat}) if (thermostat)

      addNewSceneToScene: (scene) ->
        @scene.push({scene, action: 'sceneExecute'}) if (scene)

      onChange: ->
        if not @disabled
          operationsWithActions = @scene.filter((operation) -> !!operation.action)
          sceneString = operationsWithActions.map((operation) -> "#{(operation.channel or operation.thermostat or operation.scene).id};#{operation.action}").join('|')
          @ngModel.$setViewValue(sceneString)

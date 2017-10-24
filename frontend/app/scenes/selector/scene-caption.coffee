angular.module('supla-scripts').component 'sceneCaption',
  template: '{{ $ctrl.caption }}'
  bindings:
    scene: '<'
  controller: (Channels, $q, channelLabelFilter, CHANNEL_AVAILABLE_ACTIONS) ->
    new class
      scene: []

      $onInit: ->
        actions = @scene?.actions or @scene
        if angular.isString(actions)
          sceneStrings = actions.split('|').filter((e) -> !!e)
          promises = sceneStrings.map((sceneString) -> Channels.get(sceneString.split(';')[0]))
          @loadingChannels = yes
          $q.all(promises).then (channels) =>
            @loadingChannels = no
            @caption = sceneStrings
              .map (sceneString, index) =>
                channel = channels[index]
                action = sceneString.split(';')[1]
                availableActions = CHANNEL_AVAILABLE_ACTIONS[channel.function.name]
                actionDefinition = availableActions.filter((a) -> a.action == action)[0]
                if actionDefinition
                  "#{actionDefinition.label} #{channelLabelFilter(channel)}"
              .filter((a) -> !!a)
              .join(', ')

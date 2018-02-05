angular.module('supla-scripts').component 'sceneCaption',
  template: '{{ $ctrl.caption }}'
  bindings:
    scene: '<'
  controller: (Channels, $q, channelLabelFilter, CHANNEL_AVAILABLE_ACTIONS) ->
    new class
      scene: []

      $onInit: ->
        actions = @scene?.actions or @scene
        actions = {0: actions} if not angular.isObject(actions)
        @loadingChannels = yes
        promises = for offset, action of actions
          if angular.isString(action)
            do (offset, action) =>
              @sceneStringToCaption(action).then (partialCaption) ->
                if +offset > 0
                  "po #{offset}s #{partialCaption}"
                else
                  partialCaption
        $q.all(promises).then (captions) =>
          @loadingChannels = no
          @caption = captions.filter((a) -> !!a).join('; ')


      sceneStringToCaption: (actions) ->
        sceneStrings = actions.split('|').filter((e) -> !!e)
        promises = sceneStrings.map((sceneString) -> Channels.get(sceneString.split(';')[0]))
        $q.all(promises).then (channels) ->
          sceneStrings
            .map (sceneString, index) ->
              channel = channels[index]
              action = sceneString.split(';')[1]
              availableActions = CHANNEL_AVAILABLE_ACTIONS[channel?.function.name]
              actionDefinition = availableActions?.filter((a) -> a.action == action)[0]
              if actionDefinition
                "#{actionDefinition.label} #{channelLabelFilter(channel)}"
            .filter((a) -> !!a)
            .join(', ')

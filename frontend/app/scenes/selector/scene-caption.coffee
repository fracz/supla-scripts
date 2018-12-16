angular.module('supla-scripts').component 'sceneCaption',
  template: '{{ $ctrl.caption }}'
  bindings:
    scene: '<'
  controller: (Channels, $q, channelLabelFilter, CHANNEL_AVAILABLE_ACTIONS, Thermostats, Scenes) ->
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
        promises = sceneStrings.map (sceneString) ->
          parts = sceneString.split(';')
          if parts[1].indexOf('thermostat') == 0
            Thermostats.get(parts[0], simple: yes)
          else if parts[1].indexOf('scene') == 0
            Scenes.get(parts[0])
          else
            Channels.get(parts[0])
        $q.all(promises).then (entities) ->
          sceneStrings
            .map (sceneString, index) ->
              entity = entities[index]
              parts = sceneString.split(';')
              action = parts[1]
              if parts[1].indexOf('thermostat') == 0
                'zmień profil termostatu ' + entity.label
              else if parts[1].indexOf('scene') == 0
                "wykonaj scenę \"#{entity.label}\""
              else
                availableActions = CHANNEL_AVAILABLE_ACTIONS[entity?.function.name]
                actionDefinition = availableActions?.filter((a) -> a.action == action)[0]
                if actionDefinition
                  "#{actionDefinition.label} #{channelLabelFilter(entity)}"
            .filter((a) -> !!a)
            .join(', ')

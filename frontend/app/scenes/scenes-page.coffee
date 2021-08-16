angular.module('supla-scripts').component 'scenesPage',
  templateUrl: 'app/scenes/scenes-page.html'
  controller: (Scenes, SceneGroups, $state, $q) ->
    $onInit: ->
      Scenes.getList().then (@scenes) =>
        SceneGroups.getList().then (sceneGroups) =>
          for sceneGroup in sceneGroups
            sceneGroup.scenes = (scene for scene in @scenes when scene.groupId == sceneGroup.id)
          firstPositiveIndex = sceneGroups.findIndex((g) -> g.ordinalNumber >= 0)
          firstPositiveIndex = sceneGroups.length if firstPositiveIndex == -1
          sceneGroups.splice(firstPositiveIndex, 0, {id: 'default', scenes: (scene for scene in @scenes when not scene.groupId)})
          @sceneGroups = sceneGroups

    addNewScene: (scene) ->
      Scenes.post(scene).then (savedScene) =>
        defaultGroup = (group for group in @sceneGroups when group.id == 'default')[0]
        defaultGroup.scenes.push(savedScene)
        $state.go('scenes.details', {id: savedScene.id})

    addNewSceneGroup: ->
      $q.when swal
        type: 'question'
        title: 'Podaj nazwę grupy'
        showCancelButton: yes
        showConfirmButton: yes
        cancelButtonText: 'Anuluj'
        confirmButtonText: 'Zapisz'
        showLoaderOnConfirm: true
        input: 'text'
        preConfirm: (label) =>
          SceneGroups.post({label})
      .then (sceneGroup) =>
        sceneGroup = sceneGroup.plain()
        sceneGroup.scenes = []
        @sceneGroups.push(sceneGroup)

    editSceneGroupName: (sceneGroup) ->
      $q.when swal
        type: 'question'
        title: 'Podaj nową nazwę grupy'
        showCancelButton: yes
        showConfirmButton: yes
        cancelButtonText: 'Anuluj'
        confirmButtonText: 'Zapisz'
        showLoaderOnConfirm: true
        input: 'text'
        inputValue: sceneGroup.label
        preConfirm: (label) =>
          sceneGroup.label = label
          sceneGroup.save()

    removeEmptyGroup: (sceneGroup) ->
      sceneGroup.remove()
      @sceneGroups.splice(@sceneGroups.indexOf(sceneGroup), 1)

    toggleSceneGroup: (sceneGroup) ->
      sceneGroup.collapsed = not sceneGroup.collapsed
      sceneGroup.save()

    sceneOrderChanged: (container, index) ->
      container.splice(index, 1)
      map = []
      for sceneGroup in @sceneGroups
        sceneIds = (scene.id for scene in sceneGroup.scenes)
        map.push([sceneGroup.id, sceneIds...])
      SceneGroups.one().customPATCH({map})

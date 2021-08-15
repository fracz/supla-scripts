angular.module('supla-scripts').component 'scenesPage',
  templateUrl: 'app/scenes/scenes-page.html'
  controller: (Scenes, $state, $q) ->
    $onInit: ->
      Scenes.getList().then (@scenes) =>
        @sceneGroups = [
          {id: 'default', scenes: @scenes}
        ]

    addNewScene: (scene) ->
      Scenes.post(scene).then (savedScene) =>
        @scenes.push(savedScene)
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
        preConfirm: (name) =>
          $q.when(name)
#          @user.withHttpConfig(skipErrorHandler: yes).patch(delete: password).catch(=> $q.reject('Podane hasło jest niepoprawne'))
      .then (name) =>
        @sceneGroups.push({id: 1, name, scenes: []})


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
        preConfirm: (name) =>
          $q.when(name)
#          @user.withHttpConfig(skipErrorHandler: yes).patch(delete: password).catch(=> $q.reject('Podane hasło jest niepoprawne'))
      .then (name) =>
        sceneGroup.name = name if name.trim()

    removeEmptyGroup: (sceneGroup) ->
      @sceneGroups.splice(@sceneGroups.indexOf(sceneGroup), 1)

    toggleSceneGroup: (sceneGroup) ->
      sceneGroup.collapsed = not sceneGroup.collapsed

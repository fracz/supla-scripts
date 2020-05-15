angular.module('supla-scripts').component 'reactionsPage',
  templateUrl: 'app/reactions/reactions-page.html'
  controller: (Reactions, $state) ->
    $onInit: ->
      Reactions.getList().then((@reactions) =>)

    addNewReaction: (scene) ->
      Reactions.post(scene).then (savedReaction) =>
        @reactions.push(savedReaction)
#        $state.go('scenes.details', {id: savedScene.id})

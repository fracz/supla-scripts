angular.module('supla-scripts').component 'sceneForm',
  templateUrl: 'app/scene/form/scene-form.html'
  bindings:
    scene: '<'
    onSubmit: '&'
    onCancel: '&'
  controller: ->
    new class
      $onInit: ->
        if @scene
          @scene = angular.copy(@scene.plain?() or @scene)
          @scene.generateSlug = !!@scene.slug
        else
          @scene = {}

      submit: ->
        savedScene = angular.copy(@scene)
        @onSubmit({savedScene})

angular.module('supla-scripts').component 'copyButton',
  template: '<a clipboard
                     supported="supported"
                     ng-hide="!supported"
                     text="$ctrl.text"
                     on-copied="$ctrl.onCopied()"
                     ng-class="{
                        \'btn btn-default\': !$ctrl.copied && !$ctrl.link,
                        \'btn btn-success\': $ctrl.copied && !$ctrl.link,
                     }">
            <fa name="{{ $ctrl.copied ? \'check\' : \'clipboard\' }}" fw></fa>
            <span ng-if="$ctrl.copied">{{ $ctrl.labelCopied }}</span>
            <span ng-else>{{ $ctrl.label }}</span>
    </a>'
  bindings:
    text: '<'
    label: '@'
    labelCopied: '@'
    timeout: '<'
    link: '<'
  controller: ($timeout) ->
    new class
      $onInit: ->
        @label ?= 'Kopiuj'
        @labelCopied ?= 'Skopiowano'
        @timeout ?= 3000

      onCopied: ->
        @copied = yes
        $timeout.cancel(@resetCopied) if @resetCopied
        @resetCopied = $timeout((=> @copied = no), @timeout)


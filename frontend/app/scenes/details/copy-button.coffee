angular.module('supla-scripts').component 'copyButton',
  template: '<button clipboard
                     type="button"
                     supported="supported"
                     ng-hide="!supported"
                     text="$ctrl.text"
                     on-copied="$ctrl.onCopied()"
                     class="btn"
                     ng-class="{\'btn-default\': !$ctrl.copied, \'btn-success\': $ctrl.copied}">
            <fa name="{{ $ctrl.copied ? \'check\' : \'clipboard\' }}" fw></fa>
            <span ng-if="$ctrl.copied">{{ $ctrl.labelCopied }}</span>
            <span ng-else>{{ $ctrl.label }}</span>
    </button>'
  bindings:
    text: '<'
    label: '@'
    labelCopied: '@'
    timeout: '<'
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


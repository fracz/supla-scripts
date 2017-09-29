angular.module('supla-scripts').component 'faCheckbox',
  template: '<fa name="square-o" fw ng-class="{checked: $ctrl.checked}"></fa>'
  bindings:
    checked: '<'

angular.module('supla-scripts').component 'faRadio',
  template: '<fa name="circle-o" fw ng-class="{checked: $ctrl.checked}"></fa>'
  bindings:
    checked: '<'

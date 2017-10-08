angular.module('supla-scripts').component 'voiceCommandDisplay',
  templateUrl: 'app/voice/display/voice-command-display.html'
  bindings:
    voiceCommand: '<'
    onEdit: '&'
    onDelete: '&'

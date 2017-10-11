angular.module('supla-scripts').component 'voiceCommandDisplay',
  templateUrl: 'app/voice/details/voice-command-display.html'
  bindings:
    voiceCommand: '<'
    onEdit: '&'
    onDelete: '&'

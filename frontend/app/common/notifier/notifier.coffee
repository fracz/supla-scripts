angular.module('supla-scripts').service 'Notifier', (toastr) ->
  notify = (type, title, body) ->
    if not body
      body = title
      title = undefined
    toastr[type](body, title)

  success: (title, body) ->
    notify('success', title, body)

  info: (title, body) ->
    notify('info', title, body)

  error: (title, body) ->
    notify('error', title, body)

  warning: (title, body) ->
    notify('warning', title, body)

.config (toastrConfig) ->
  angular.extend toastrConfig,
    newestOnTop: no
    positionClass: 'toast-bottom-right'
    preventDuplicates: no
    preventOpenDuplicates: yes

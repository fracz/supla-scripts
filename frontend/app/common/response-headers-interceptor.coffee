angular.module('supla-scripts').config (RestangularProvider) ->
  CUSTOM_HEADER_PREFIX = 'SS-'

  customHeaderPrefixLower = CUSTOM_HEADER_PREFIX.toLowerCase()
  customHeaderPrefixLength = customHeaderPrefixLower.length

  isNumeric = (value) -> /^[0-9]+$/.test(value)
  isBoolean = (value) -> /^((true)|(false))$/i.test(value)

  RestangularProvider.addResponseInterceptor (data, operation, what, url, response) ->
    for header, value of response.headers() when header.indexOf(customHeaderPrefixLower) is 0
      valueName = header.substr(customHeaderPrefixLength).replace(/-([a-z])/g, (g) -> g[1].toUpperCase())
      if isNumeric(value)
        value = +value
      else if isBoolean(value)
        value = value.toLowerCase() is 'true'
      else if value.indexOf('{') == 0 and (decodedValue = try angular.fromJson(value))
        value = decodedValue
      else
# header values are URLencoded, decode them here... https://stackoverflow.com/a/24417399/878514
        value = value.replace(/\+/g, '%20');
        value = decodeURIComponent(value)
      data = {} if not data
      data[valueName] ?= value
    data

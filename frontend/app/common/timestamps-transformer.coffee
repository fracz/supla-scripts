angular.module('supla-scripts').value 'TimestampsTransformer', (entity, timestampProperties = []) ->
  timestampProperties.push('createdAt', 'updatedAt')
  for property in timestampProperties
    entity[property] = moment(entity[property]).toDate() if entity[property]
  entity

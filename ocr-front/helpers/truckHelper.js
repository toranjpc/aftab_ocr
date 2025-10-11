import { get as getSafe } from 'lodash'

function getBulkStatus(truck) {}

function getContainerStatus(truck) {
  const tabarInvoice = getSafe(truck, 'invoice', false)

  if (tabarInvoice) return 100

  return 0
}

function truckStatus(truck) {
  const truckLoadType = 'container'

  const map = {
    container: getContainerStatus,
    bulk: getBulkStatus,
  }

  return map[truckLoadType](truck)
}

function statusMessage(truck) {
  const map = {
    100: 'اطلاعات فاکتور کامیون تایید شد',
    30: 'اطلاعات فاکتور کامیون با کدکانتینر تایید شد',
    50: 'اطلاعات فاکتور کامیون با کدکانتینر و پلاک متفاوت است',
    80: 'اطلاعات فاکتور کامیون با پلاک تایید شد' + ' (احتمال استریپ)',
    0: 'اطلاعات فاکتور کامیون تایید نشد',
  }

  return truck
    ? getSafe(map, truckStatus(truck), '---------------')
    : '------------------'
}

function setChipStatus(truck) {
  const map = {
    100: 'تایید شد',
    0: 'تایید نشد',
  }

  return truck
    ? getSafe(map, truckStatus(truck), '---------------')
    : '------------------'
}

function setChipColor(truck) {
  const map = {
    100: 'success',
    30: 'yellow',
    50: 'purple',
    80: 'lightgreen',
    0: 'error',
  }

  return truck ? getSafe(map, truckStatus(truck), 'white') : 'white'
}

function statusColor(truck) {
  const map = {
    100: 'green',
    30: 'yellow',
    50: 'purple',
    80: 'lightgreen',
    0: 'red',
  }

  return truck ? getSafe(map, truckStatus(truck), 'white') : 'white'
}

function isContainer(truck) {
  return getSafe(truck, 'bijacs[0].type') === 'ccs'
}

export default {
  statusMessage,
  truckStatus,
  statusColor,
  isContainer,
  setChipStatus,
  setChipColor,
}

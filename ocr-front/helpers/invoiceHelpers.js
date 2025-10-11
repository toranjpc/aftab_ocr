import Num2persian from 'num2persian'
import { get as getSafe } from 'lodash'
import { reverseToString as plateToString } from '~/helpers/NormalizeVehicleNumber'

export const priceAfterOff = (item) => {
  return item.item_unit - item.off
}

const taxPercent = (item) => {
  return item.item_tax / 100
}

export function price(item, formatted = false) {
  const result = item.item_unit * item.item_number

  return formatted ? formatNumber(result) : result
}

export function tax(item, formatted = false) {
  const result = item.item_number * (priceAfterOff(item) * taxPercent(item))

  return formatted ? formatNumber(result) : result
}

export function off(item, formatted = false) {
  const result = item.off * item.item_number

  return formatted ? formatNumber(result) : result
}

export function total(item, formatted = false) {
  const result =
    item.item_number *
    (priceAfterOff(item) + priceAfterOff(item) * taxPercent(item))

  return formatted ? formatNumber(result) : result
}

export function invoiceNumber(invoiceItems, formatted = false) {
  const result = invoiceItems.reduce((sum, item) => sum + item.item_number, 0)

  return formatted ? formatNumber(result) : result
}

export function invoiceNumberGcoms(invoiceItems, formatted = false) {
  const result = invoiceItems.reduce((sum, item) => {
    if (item?.type === 'full_container' || item?.type === 'empty_container') {
      return sum + item.item_number
    }
    return sum
  }, 0)

  return formatted ? formatNumber(result) : result
}
export function invoiceGcomsWight(invoiceItems, formatted = false) {
  const result = invoiceItems.reduce((sum, item) => {
    if (item?.type === 'bulk' || item?.type === 'bulk_compensation') {
      return sum + item.item_number
    }
    return sum
  }, 0)

  return formatted ? formatNumber(result) : result
}

export function invoicePrice(invoiceItems, formatted = false) {
  const result = invoiceItems.reduce((sum, item) => sum + price(item), 0)

  return formatted ? formatNumber(result) : result
}

export function invoiceTax(invoiceItems, formatted = false) {
  const result = invoiceItems.reduce((sum, item) => sum + tax(item), 0)

  return formatted ? formatNumber(result) : result
}

export function invoiceOff(invoiceItems, formatted = false) {
  const result = invoiceItems.reduce((sum, item) => sum + off(item), 0)

  return formatted ? formatNumber(result) : result
}

export function invoiceTotal(invoiceItems, formatted = false) {
  const result = invoiceItems.reduce((sum, item) => sum + total(item), 0)

  return formatted ? formatNumber(result) : result
}

export function formatNumber(inputNumber) {
  if (typeof inputNumber !== 'number') return

  return inputNumber.toLocaleString('en-US')
}

export function showDate(d = null) {
  let date = null
  if (d !== null) {
    if (d.includes('/') || d.includes('-')) date = new Date(d)
    else {
      date = new Date()
    }
  } else {
    date = new Date()
  }
  const options = { year: 'numeric', month: 'numeric', day: 'numeric' }
  return date?.toLocaleString('fa-IR', options)
}

const convertDate = (date) => showDate(date).split('/').reverse().join('/')

export const prepareInvoiceToPrint = ({ invoice, invoiceItems }) => {
  const customer = invoice.customer
  console.log(getSafe(invoice, 'station_payment', []).map((i) => i.pay_number))
  return {
    created_at: convertDate(invoice.created_at),
    vehicle_number: plateToString(
      invoice?.station_gate?.vehicle_number,
      invoice?.station_gate?.vehicle_type
    ),
    container_number: invoice.container_number
      ? invoice.container_number
      : '...............',
    invoice_number: invoice.invoice_number,
    customer_name: customer?.title,
    code_eghtesadi: customer?.code_eghtesadi || customer?.code_eghtesadi,
    shenase_meli: customer?.shenase_meli,
    shomare_sabt: customer?.shomare_sabt,
    phone: invoice?.agent?.phone,
    postal_code: customer?.postal_code,
    address: customer?.address,
    invoice_items: invoiceItems.map((item, index) => {
      return {
        ...item,
        index: index + 1,
        item_name: item.item_name.toLocaleString('fa-IR'),
        item_unit: formatNumber(item.item_unit),
        price: price(item, true),
        vat: tax(item, true),
        off: off(item, true),
        afterOff: formatNumber(priceAfterOff(item) * item.item_number),
        all: total(item, true),
      }
    }),
    sN: invoiceNumber(invoiceItems),
    cN: invoice?.container_number,
    sumPrice: invoicePrice(invoiceItems, true),
    sumTax: invoiceTax(invoiceItems, true),
    sumOff: invoiceOff(invoiceItems, true),
    total: invoiceTotal(invoiceItems, true),
    afterOff: formatNumber(
      invoicePrice(invoiceItems) - invoiceOff(invoiceItems)
    ),
    perian: Num2persian(invoiceTotal(invoiceItems)),
    iType: invoice?.invoice_type === 'bulk' ? 'وزن خالص (تن)' : 'تعداد',
    title: invoice?.invoice_number?.startsWith('G')
      ? '(بخش فله)'
      : '(بخش سکو ارزیابی)',
    payments: getSafe(invoice, 'station_payment', []).map((i) => i.pay_number),
  }
}

export function showTime(d) {
  const date = new Date(d)
  // return d
  const options = { hour: 'numeric', minute: 'numeric' }

  return date?.toLocaleString('fa-IR', options)
}

export const prepareDocumentToPrint = ({ invoice }) => {
  return {
    created_at: convertDate(invoice.created_at),
    time: showTime(invoice.created_at),
    invoice_number: invoice.invoice_number,
    vehicle_number: plateToString(
      invoice?.station_gate?.vehicle_number,
      invoice?.station_gate?.vehicle_type
    ),
    container_number: invoice.container_number
      ? invoice.container_number
      : '...............',
    station_id: invoice?.station_gate?.station_id
      ? invoice?.station_gate?.station_id
      : '........',
    seal_status:
      invoice?.station_gate?.seal_status === 'well'
        ? 'سالم'
        : invoice?.station_gate?.seal_status === 'faulty'
        ? 'معیوب'
        : 'فاقد پلمپ',
    phone: invoice?.agent?.phone,
    arz_yab: invoice?.station_gate?.evaluator?.name,
    agent: invoice.agent_id
      ? 'نماینده صاحب کالا آقا/خانم ' + invoice?.agent?.name
      : 'صاحب کالا ' + invoice?.customer?.type !== 'juridical'
      ? 'آقا/خانم'
      : ' شرکت' + +invoice.customer?.title,
  }
}

export const PaperForPayAsDraft = ({ invoice }) => {
  return {
    created_at: convertDate(invoice.created_at),
    time: showTime(invoice.created_at),
    invoice_number: invoice.invoice_number,
    total: formatNumber(invoice.total),
  }
}

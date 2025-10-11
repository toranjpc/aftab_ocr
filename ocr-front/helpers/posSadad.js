import axios from 'axios'

export default {
  async send(amount, orderId, saleId) {
    console.log('Starting the send function...')
    const pcposUrl = 'http://localhost:8050/api/Sale'
    const reqParams = this.createRequestBody(amount, orderId, saleId)
    try {
      const { data } = await axios.post(pcposUrl, reqParams, {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      console.log(data, 'Response Data:')

      if (
        data.PcPosStatusCode === 6 ||
        data.PcPosStatusCode === 0 ||
        data.PcPosStatusCode === 3
      ) {
        return {
          color: 'error',
          text: data.PcPosStatus,
        }
      } else if (data.OptionalField !== null)
        return {
          color: 'error',
          text: data.OptionalField,
        }
      else if (data.ResponseCodeMessage !== null)
        return {
          color: 'success',
          text: data.ResponseCodeMessage,
        }
    } catch (error) {
      return {
        color: 'error',
        text: 'خطا در پردازش تراکنش' + error,
      }
    }
  },

  createRequestBody(amount, orderId, saleId) {
    return {
      ConnectionType: 'Lan',
      DeviceIp: '172.16.11.80',
      DevicePort: '8888',
      SerialPort: undefined,
      MultiAccount: undefined,
      DivideType: '7',
      Amount: amount,
      OrderId: orderId,
      SaleId: saleId,
      DeviceType: '0',
      TerminalId: undefined,
      MerchantId: undefined,
    }
  },
}

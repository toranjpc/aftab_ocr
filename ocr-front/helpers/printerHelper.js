import connection from './osConnector'

const print = ({ template, context, count }) => {
  if (!connection.socket) return

  const preparedData = JSON.stringify({
    event: 'print',
    template,
    context,
    count: count || 1,
  })

  connection.socket.send(preparedData)
}

export default { print }

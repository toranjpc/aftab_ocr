import ReconnectingWebSocket from 'reconnecting-websocket'

const connection = {
  socket: {},
}

export const connector = (callback = () => { }, ip = 'localhost') => {
  const HOST = ip // Destination IP address
  const PORT = 8765 // Destination port

  connection.socket = new ReconnectingWebSocket(`ws://${HOST}:${PORT}`)

  connection.socket.addEventListener('open', () => {
    callback(connection.socket)
  })

  callback(connection.socket)

  return connection.socket
}

export default connection

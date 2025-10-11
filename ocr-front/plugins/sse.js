import { EventSourcePolyfill } from 'event-source-polyfill';

export default function (context, inject) {
    const sseService = {
        connections: {},

        connect(url, onMessage) {
            this.disconnect(url); // اگر قبلاً برای این URL اتصالی بود، آن را ببند

            const fullUrl = process.env.baseURL + url + (url.includes('?') ? '&' : '?') + 't=' + Date.now();

            const sse = new EventSourcePolyfill(fullUrl, {
                headers: {
                    'Authorization': context.$auth.getToken('local')
                }
            });

            sse.onmessage = ({ data }) => {
                if (['"ADD"', '"heart"'].includes(data)) return;
                onMessage(data);
            };

            sse.onerror = (error) => {
                console.error('SSE Error:', error);
                sse.close();
                delete this.connections[url];
            };

            this.connections[url] = sse;
        },

        disconnect(url) {
            if (this.connections[url]) {
                this.connections[url].close();
                delete this.connections[url];
            }
        },

        disconnectAll() {
            Object.keys(this.connections).forEach(url => {
                this.connections[url].close();
                delete this.connections[url];
            });
        }
    };

    inject('sse', sseService);
}
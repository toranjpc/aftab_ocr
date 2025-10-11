FROM node:16-alpine

WORKDIR /app

COPY package.json yarn.lock ./

RUN yarn

COPY . .

RUN yarn build

RUN npm install -g pm2

EXPOSE 3232

CMD ["pm2-runtime", "start", "npm", "--name", "my-app", "--", "run", "start"]

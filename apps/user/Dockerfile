FROM node:26-alpine

RUN apk add --no-cache openssl python3 make g++

WORKDIR /app

EXPOSE 3000

CMD ["npm", "run", "start:dev"]
